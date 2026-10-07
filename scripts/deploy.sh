#!/bin/bash
#
# Локальный деплой luxsol.sk: www/ -> боевой сервер по SSH
#
# На сервере git не заведён, поэтому код переносится rsync-ом.
# По умолчанию НИЧЕГО НЕ ПИШЕТ - показывает, что изменится.
#
#   ./scripts/deploy.sh                       предпросмотр всего local/
#   ./scripts/deploy.sh --apply               залить
#   ./scripts/deploy.sh catalog/index.php     предпросмотр одного файла
#   ./scripts/deploy.sh --apply --clear-cache залить и сбросить кеш Битрикса
#   ./scripts/deploy.sh --apply --delete      залить, удаляя лишнее на сервере
#   ./scripts/deploy.sh --pull                предпросмотр: что нового НА СЕРВЕРЕ
#                                             (локальные файлы не трогает)
#   ./scripts/deploy.sh --pull --apply        забрать с сервера в локальную копию
#
# Забор с --apply перезаписывает локальные файлы. Перед этим снимается архив
# в .pull-backups/ - оттуда можно достать незалитые правки.
#
# Перед заливкой стоит выполнить --pull: если на сервере правили руками,
# локальная копия может быть старее, и деплой откатит чужие изменения.
#
# Перед отправкой каждый .php проверяется на синтаксис в локальном контейнере.
# Перед записью на сервере создаётся архив затрагиваемых путей.

set -euo pipefail

SSH_HOST="root@152.53.135.245"
REMOTE_ROOT="/var/www/html/bx-site"
REMOTE_OWNER="www-data:www-data"
PHP_CONTAINER="luxsol-sk-php-fpm"
BACKUP_DIR="/root/deploy-backups"

SITE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCAL_ROOT="$SITE_DIR/www"

# Пути по умолчанию. Всё остальное на сервере не трогаем
DEFAULT_PATHS=(local)

# Никогда не отправляется: настройки окружения, боевые ключи, мусор
EXCLUDES=(
    "local/php_interface/room/settings.local.php"
    "local/php_interface/include/sale_payment/gpweb/keys/"
    "local/tools/psc/*.xlsx"
    "*.prod-backup"
    ".DS_Store"
    "*.log"
)

APPLY=0
PULL=0
DELETE=0
CLEAR_CACHE=0
PATHS=()

for arg in "$@"; do
    case "$arg" in
        --apply)       APPLY=1 ;;
        --pull)        PULL=1 ;;
        --delete)      DELETE=1 ;;
        --clear-cache) CLEAR_CACHE=1 ;;
        -*)            echo "неизвестный ключ: $arg"; exit 1 ;;
        *)             PATHS+=("$arg") ;;
    esac
done

[ ${#PATHS[@]} -eq 0 ] && PATHS=("${DEFAULT_PATHS[@]}")

if [ $PULL -eq 0 ]; then
    for p in "${PATHS[@]}"; do
        if [ ! -e "$LOCAL_ROOT/$p" ]; then
            echo "нет такого пути локально: www/$p"; exit 1
        fi
    done
fi

echo "Сервер:  $SSH_HOST:$REMOTE_ROOT"
echo "Пути:    ${PATHS[*]}"
echo "Направление: $([ $PULL -eq 1 ] && echo 'сервер -> локальная копия' || echo 'локальная копия -> сервер')"
echo "Режим:   $([ $APPLY -eq 1 ] && echo 'ЗАПИСЬ' || echo 'предпросмотр')$([ $DELETE -eq 1 ] && echo ' + удаление лишнего')"
echo "--------------------------------------------------------------------"

# ------------------------------------------------- проверка синтаксиса
if [ $PULL -eq 1 ]; then
    echo "Забираем с сервера, проверка синтаксиса не нужна"
elif docker ps --format '{{.Names}}' | grep -qx "$PHP_CONTAINER"; then
    echo "Проверка синтаксиса PHP в контейнере..."
    ERRORS=0
    while IFS= read -r f; do
        rel="${f#"$LOCAL_ROOT"/}"
        if ! out=$(docker exec "$PHP_CONTAINER" php -l "/var/www/www/$rel" 2>&1 | grep -v "JIT\|Xdebug"); then
            echo "  ОШИБКА: $rel"; echo "$out" | head -3; ERRORS=$((ERRORS + 1))
        fi
    done < <(for p in "${PATHS[@]}"; do find "$LOCAL_ROOT/$p" -name '*.php' -type f; done)
    if [ $ERRORS -gt 0 ]; then
        echo "Найдено файлов с ошибками: $ERRORS. Деплой остановлен."; exit 1
    fi
    echo "  синтаксис в порядке"
else
    echo "ВНИМАНИЕ: контейнер $PHP_CONTAINER не запущен, синтаксис не проверен"
fi

RSYNC_ARGS=(-rlptD --itemize-changes --human-readable)
for e in "${EXCLUDES[@]}"; do RSYNC_ARGS+=(--exclude "$e"); done
[ $DELETE -eq 1 ] && RSYNC_ARGS+=(--delete)

# ------------------------------------------------- забор с сервера
if [ $PULL -eq 1 ]; then
    # Забор затирает локальные правки, если их ещё не залили. Архив снимается
    # всегда: восстановить из него дешевле, чем писать код заново
    if [ $APPLY -eq 1 ]; then
        LOCAL_BACKUP="$SITE_DIR/.pull-backups/local_$(date +%Y%m%d_%H%M%S).tar.gz"
        mkdir -p "$(dirname "$LOCAL_BACKUP")"
        tar czf "$LOCAL_BACKUP" -C "$LOCAL_ROOT" "${PATHS[@]}"
        echo "Копия локальных файлов: $LOCAL_BACKUP"
    fi

    # Предпросмотр НЕ через rsync -n. В macOS вместо rsync стоит openrsync,
    # и при заборе с сервера он игнорирует -n и пишет файлы: так дважды
    # пропадали незалитые правки. Поэтому без --apply забираем во временную
    # папку, сравнивая с локальной копией (--compare-dest), и удаляем её.
    # Локальная копия при этом не меняется ни при какой версии rsync
    PREVIEW_DIR=""
    if [ $APPLY -eq 0 ]; then
        PREVIEW_DIR=$(mktemp -d "${TMPDIR:-/tmp}/luxsol-pull-preview.XXXXXX")
        trap 'rm -rf "$PREVIEW_DIR"' EXIT
    fi

    for p in "${PATHS[@]}"; do
        if [ $APPLY -eq 1 ]; then
            rsync "${RSYNC_ARGS[@]}" -e "ssh -o BatchMode=yes" \
                "$SSH_HOST:$REMOTE_ROOT/$p" "$LOCAL_ROOT/$(dirname "$p")/" | grep -vE "^$|^receiving|^total|^sent " || true
        else
            mkdir -p "$PREVIEW_DIR/$(dirname "$p")"
            # Строки cd+++ - папки, которые создаются во временной копии всегда
            rsync "${RSYNC_ARGS[@]}" --compare-dest="$LOCAL_ROOT/$(dirname "$p")/" -e "ssh -o BatchMode=yes" \
                "$SSH_HOST:$REMOTE_ROOT/$p" "$PREVIEW_DIR/$(dirname "$p")/" | grep -vE "^$|^receiving|^total|^sent |^cd" || true
        fi
    done
    echo "--------------------------------------------------------------------"
    [ $APPLY -eq 0 ] && echo "Ничего не записано. Для забора добавьте --apply" || echo "Локальная копия обновлена"
    exit 0
fi

# ------------------------------------------------- предпросмотр
if [ $APPLY -eq 0 ]; then
    echo "Что изменится (ничего не пишется):"
    for p in "${PATHS[@]}"; do
        rsync -n "${RSYNC_ARGS[@]}" -e "ssh -o BatchMode=yes" \
            "$LOCAL_ROOT/$p" "$SSH_HOST:$REMOTE_ROOT/$(dirname "$p")/" | grep -vE "^$|^sending|^total|^sent " || true
    done
    echo "--------------------------------------------------------------------"
    echo "Для заливки добавьте --apply"
    exit 0
fi

# ------------------------------------------------- резервная копия
STAMP=$(date +%Y%m%d_%H%M%S)
echo "Резервная копия на сервере..."
ssh -o BatchMode=yes "$SSH_HOST" "umask 077; mkdir -p $BACKUP_DIR && cd $REMOTE_ROOT && tar czf $BACKUP_DIR/luxsol_$STAMP.tar.gz ${PATHS[*]} 2>/dev/null; ls -lh $BACKUP_DIR/luxsol_$STAMP.tar.gz"

# ------------------------------------------------- заливка
echo "Заливка..."
for p in "${PATHS[@]}"; do
    rsync "${RSYNC_ARGS[@]}" -e "ssh -o BatchMode=yes" \
        "$LOCAL_ROOT/$p" "$SSH_HOST:$REMOTE_ROOT/$(dirname "$p")/" | grep -vE "^$|^sending|^total|^sent " || true
done

# rsync из macOS (версия 2.6.9) не умеет --chown, выставляем владельца отдельно
echo "Владелец файлов..."
ssh -o BatchMode=yes "$SSH_HOST" "cd $REMOTE_ROOT && chown -R $REMOTE_OWNER ${PATHS[*]} && echo '  $REMOTE_OWNER'"

# ------------------------------------------------- сброс кеша
if [ $CLEAR_CACHE -eq 1 ]; then
    echo "Сброс кеша Битрикса..."
    ssh -o BatchMode=yes "$SSH_HOST" "cd $REMOTE_ROOT && rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/* 2>/dev/null; echo '  кеш очищен'"
fi

# ------------------------------------------------- проверка
echo "Проверка сайта..."
for u in / /catalog/ /kontakt/; do
    code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 30 "https://luxsol.sk$u")
    printf "  %-12s %s\n" "$u" "$code"
done

echo "--------------------------------------------------------------------"
echo "Готово. Откат: ssh $SSH_HOST 'cd $REMOTE_ROOT && tar xzf $BACKUP_DIR/luxsol_$STAMP.tar.gz'"

<?php

/**
 * Онлайн-отказ от договора (Odstúpenie od zmluvy): письма и журнал заявлений
 *
 * Форма на /odstupenie-od-zmluvy/ отправляет заявление через
 * /ajax/odstupenie.php. Для неё нужны:
 *   - почтовое событие ODSTUPENIE_OD_ZMLUVY с двумя шаблонами: офису со всеми
 *     данными и клиенту с подтверждением получения (VOP п. 6.8 требует
 *     подтвердить получение «на trvanlivom médiu» - письмо это и есть);
 *   - инфоблок-журнал withdrawals: если письмо потеряется, запись о том,
 *     когда клиент отказался от договора, останется в админке.
 *
 * Шаблоны в базе, в коде их нет, поэтому заводятся этим скриптом - локально
 * и на боевом одинаково. Повторный запуск ничего не дублирует, а обновляет.
 *
 * Запуск:
 *   php local/tools/mail_odstupenie.php            показать текущее состояние
 *   php local/tools/mail_odstupenie.php --apply    создать или обновить
 *   php local/tools/mail_odstupenie.php --off      выключить шаблоны писем
 */

const EVENT_NAME = 'ODSTUPENIE_OD_ZMLUVY';
const SITE = 's1';
const IBLOCK_CODE = 'withdrawals';
const IBLOCK_TYPE = 'luxol';

// Отправитель только info@: почта уходит через Zoho от этого ящика,
// с другим адресом письма не пройдут SPF/DKIM и попадут в спам
const MAIL_FROM = 'info@luxsol.sk';
const MAIL_OFFICE = 'office@luxsol.sk';

// Права на журнал: в нём персональные данные, посетителям доступа нет.
// Контент-менеджерам - как у инфоблока «Заказать звонок»
const IBLOCK_RIGHTS = [
    1 => 'X', // администраторы
    2 => 'D', // все пользователи
    9 => 'W', // контент-менеджмент
];

const EVENT_DESCRIPTION = <<<TXT
#ID# - номер записи в журнале «Отказы от договора»
#DATE# - дата и время получения
#ORDER_NUMBER# - номер заказа
#NAME# - имя и фамилия
#EMAIL# - e-mail клиента
#PRODUCT# - товар, который клиент хочет вернуть
#COMMENT# - комментарий
TXT;

// Письмо офису: всё, что заполнил клиент. Ответ уходит сразу клиенту
const TEMPLATE_OFFICE = [
    'EMAIL_TO' => MAIL_OFFICE,
    'REPLY_TO' => '#EMAIL#',
    'SUBJECT' => 'Odstúpenie od zmluvy – č. objednávky: #ORDER_NUMBER#',
    'MESSAGE' => <<<TXT
Zákazník odoslal cez Luxsol.sk odstúpenie od zmluvy.

Prijaté: #DATE#
Číslo záznamu: #ID#

Číslo objednávky: #ORDER_NUMBER#
Meno a priezvisko: #NAME#
E-mail: #EMAIL#

Tovar, ktorý chce vrátiť:
#PRODUCT#

Komentár:
#COMMENT#

Zákazníkovi bolo automaticky odoslané potvrdenie o prijatí oznámenia.
Odpoveď na tento e-mail príde priamo zákazníkovi.
TXT,
];

// Письмо клиенту: подтверждаем только получение. Право на отказ и возврат
// денег офис оценивает сам - письмо не должно выглядеть как согласие
const TEMPLATE_CUSTOMER = [
    'EMAIL_TO' => '#EMAIL#',
    'REPLY_TO' => MAIL_OFFICE,
    'SUBJECT' => 'Potvrdenie prijatia odstúpenia od zmluvy – Luxsol.sk',
    'MESSAGE' => <<<TXT
Dobrý deň,

potvrdzujeme, že dňa #DATE# sme prostredníctvom online funkcie „Odstúpiť od zmluvy“ na Luxsol.sk prijali Vaše oznámenie o odstúpení od zmluvy.

Údaje, ktoré ste nám zaslali:
Číslo objednávky: #ORDER_NUMBER#
Meno a priezvisko: #NAME#
E-mail: #EMAIL#
Tovar: #PRODUCT#
Komentár: #COMMENT#

Tento e-mail potvrdzuje iba prijatie Vášho oznámenia. Nie je posúdením, či sa na objednaný tovar vzťahuje právo na odstúpenie od zmluvy, ani potvrdením vrátenia platby. Právo odstúpiť od zmluvy bez uvedenia dôvodu sa nevzťahuje najmä na tovar vyrobený na mieru alebo podľa Vašich špecifikácií (bod 6.2 Všeobecných obchodných podmienok).

O ďalšom postupe Vás budeme informovať e-mailom. Ak máte otázky, odpovedzte na tento e-mail alebo nám napíšte na office@luxsol.sk.

S pozdravom
Luxsol s.r.o.
Račianska 66, 831 02 Bratislava
https://luxsol.sk
TXT,
];

const PROPERTIES = [
    'ORDER_NUMBER' => ['Номер заказа', 1],
    'EMAIL' => ['E-mail', 1],
    'PRODUCT' => ['Товар', 3],
    'COMMENT' => ['Комментарий', 3],
];

if (PHP_SAPI !== 'cli') {
    die('Только из консоли' . PHP_EOL);
}

$documentRoot = realpath(__DIR__ . '/../../');

$_SERVER['DOCUMENT_ROOT'] = $documentRoot;
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require_once $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

\Bitrix\Main\Loader::includeModule('iblock');

$apply = in_array('--apply', $argv, true);
$off = in_array('--off', $argv, true);

/** Шаблоны события: [EMAIL_TO => строка из b_event_message] */
function findTemplates(): array
{
    $result = [];
    $rs = \CEventMessage::GetList('id', 'asc', ['TYPE_ID' => EVENT_NAME]);

    while ($row = $rs->Fetch()) {
        $result[$row['EMAIL_TO']] = $row;
    }

    return $result;
}

function findIblockId(): int
{
    $iblock = \CIBlock::GetList([], ['CODE' => IBLOCK_CODE, 'TYPE' => IBLOCK_TYPE, 'CHECK_PERMISSIONS' => 'N'])->Fetch();

    return $iblock ? (int)$iblock['ID'] : 0;
}

// ------------------------------------------------- текущее состояние
echo 'Текущее состояние:', PHP_EOL;

$types = [];
$rs = \CEventType::GetList(['TYPE_ID' => EVENT_NAME]);
while ($row = $rs->Fetch()) {
    $types[$row['LID']] = $row;
}
echo '  тип события ', EVENT_NAME, ': ', $types ? 'есть (' . implode(', ', array_keys($types)) . ')' : 'нет', PHP_EOL;

$templates = findTemplates();
foreach ([TEMPLATE_OFFICE, TEMPLATE_CUSTOMER] as $tpl) {
    $row = $templates[$tpl['EMAIL_TO']] ?? null;
    echo '  шаблон «', $tpl['EMAIL_TO'], '»: ',
        $row ? '#' . $row['ID'] . ($row['ACTIVE'] === 'Y' ? ', активен' : ', выключен') : 'нет', PHP_EOL;
}

$iblockId = findIblockId();
echo '  журнал «', IBLOCK_CODE, '»: ', $iblockId ? 'инфоблок #' . $iblockId : 'нет', PHP_EOL;

echo str_repeat('-', 70), PHP_EOL;

if (!$apply && !$off) {
    echo 'Ничего не изменено. Для установки добавьте --apply, для выключения писем --off', PHP_EOL;

    return;
}

// ------------------------------------------------- выключение
if ($off) {
    $em = new \CEventMessage();

    foreach ($templates as $row) {
        $em->Update($row['ID'], ['ACTIVE' => 'N']);
        echo '  шаблон #', $row['ID'], ' выключен', PHP_EOL;
    }

    echo 'Готово. Журнал заявлений не тронут', PHP_EOL;

    return;
}

// ------------------------------------------------- тип события
// Язык сайта s1 - ru, но тип заводим и на en, как у остальных событий
foreach (['ru', 'en'] as $lid) {
    $fields = [
        'LID' => $lid,
        'EVENT_NAME' => EVENT_NAME,
        'NAME' => 'Odstúpenie od zmluvy (онлайн-отказ от договора)',
        'DESCRIPTION' => EVENT_DESCRIPTION,
        'SORT' => 150,
    ];

    if (isset($types[$lid])) {
        \CEventType::Update(['ID' => $types[$lid]['ID']], $fields);
        echo '  тип события (', $lid, '): обновлён', PHP_EOL;
    } else {
        (new \CEventType())->Add($fields);
        echo '  тип события (', $lid, '): создан', PHP_EOL;
    }
}

// ------------------------------------------------- шаблоны писем
$em = new \CEventMessage();

foreach ([TEMPLATE_OFFICE, TEMPLATE_CUSTOMER] as $tpl) {
    $fields = $tpl + [
        'ACTIVE' => 'Y',
        'EVENT_NAME' => EVENT_NAME,
        'LID' => [SITE],
        'EMAIL_FROM' => MAIL_FROM,
        'BODY_TYPE' => 'text',
        'LANGUAGE_ID' => 'ru',
    ];

    $row = $templates[$tpl['EMAIL_TO']] ?? null;

    if ($row) {
        if (!$em->Update($row['ID'], $fields)) {
            echo '  ОШИБКА шаблона «', $tpl['EMAIL_TO'], '»: ', $em->LAST_ERROR, PHP_EOL;
            exit(1);
        }
        echo '  шаблон «', $tpl['EMAIL_TO'], '»: обновлён #', $row['ID'], PHP_EOL;
    } else {
        $id = $em->Add($fields);
        if (!$id) {
            echo '  ОШИБКА шаблона «', $tpl['EMAIL_TO'], '»: ', $em->LAST_ERROR, PHP_EOL;
            exit(1);
        }
        echo '  шаблон «', $tpl['EMAIL_TO'], '»: создан #', $id, PHP_EOL;
    }
}

// ------------------------------------------------- журнал заявлений
// Права ставим только существующим группам: на боевом набор может отличаться
$rights = [];
foreach (IBLOCK_RIGHTS as $groupId => $right) {
    if (\CGroup::GetByID($groupId)->Fetch()) {
        $rights[$groupId] = $right;
    }
}

$ib = new \CIBlock();
$iblockFields = [
    'ACTIVE' => 'Y',
    'NAME' => 'Отказы от договора (Odstúpenie od zmluvy)',
    'CODE' => IBLOCK_CODE,
    'IBLOCK_TYPE_ID' => IBLOCK_TYPE,
    'SITE_ID' => [SITE],
    'SORT' => 500,
    'ELEMENTS_NAME' => 'Заявления',
    'ELEMENT_NAME' => 'Заявление',
    'ELEMENT_ADD' => 'Добавить заявление',
    'ELEMENT_EDIT' => 'Изменить заявление',
    'ELEMENT_DELETE' => 'Удалить заявление',
    'LIST_PAGE_URL' => '',
    'DETAIL_PAGE_URL' => '',
    'SECTION_PAGE_URL' => '',
    // Имена и e-mail клиентов не должны попадать в поиск по сайту
    'INDEX_ELEMENT' => 'N',
    'INDEX_SECTION' => 'N',
    'WORKFLOW' => 'N',
    'GROUP_ID' => $rights,
];

if ($iblockId) {
    $ib->Update($iblockId, $iblockFields);
    echo '  журнал: инфоблок #', $iblockId, ' обновлён', PHP_EOL;
} else {
    $iblockId = (int)$ib->Add($iblockFields);
    if (!$iblockId) {
        echo '  ОШИБКА журнала: ', $ib->LAST_ERROR, PHP_EOL;
        exit(1);
    }
    echo '  журнал: создан инфоблок #', $iblockId, PHP_EOL;
}

$existing = [];
$rs = \CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CHECK_PERMISSIONS' => 'N']);
while ($row = $rs->Fetch()) {
    $existing[$row['CODE']] = $row['ID'];
}

$prop = new \CIBlockProperty();
$sort = 100;

foreach (PROPERTIES as $code => [$name, $rows]) {
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'CODE' => $code,
        'PROPERTY_TYPE' => 'S',
        'ROW_COUNT' => $rows,
        'COL_COUNT' => 60,
        'SORT' => $sort += 100,
        'ACTIVE' => 'Y',
    ];

    if (isset($existing[$code])) {
        $prop->Update($existing[$code], $fields);
    } else {
        $prop->Add($fields);
    }
}
echo '  журнал: свойства ', implode(', ', array_keys(PROPERTIES)), PHP_EOL;

\CIBlock::CleanCache($iblockId);

echo str_repeat('-', 70), PHP_EOL;
echo 'Готово. Журнал: Админка -> Контент -> Luxol -> «Отказы от договора»', PHP_EOL;

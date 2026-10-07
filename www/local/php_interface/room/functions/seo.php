<?php

/**
 * Канонический адрес текущей страницы
 *
 * Фильтры, сортировка, число товаров на странице и метки рекламных кампаний
 * создают десятки адресов с одинаковым содержимым. Канонический адрес
 * говорит поисковику, какой из них считать основным.
 *
 * Постраничная навигация сохраняется: вторая страница раздела - это
 * действительно другая страница, и склеивать её с первой не нужно.
 *
 * Домен берётся из настроек сайта, а не из заголовка Host: иначе чужой
 * запрос с подменённым заголовком подставит свой домен в canonical.
 *
 * @return string пустая строка - выводить canonical не нужно
 */
function room_canonical_url(): string
{
    if (!defined('SITE_SERVER_NAME') || SITE_SERVER_NAME === '') {
        return '';
    }

    global $APPLICATION;

    $path = (string)$APPLICATION->GetCurPage(false);

    if ($path === '') {
        return '';
    }

    // Страницы поиска канонизировать нечем: у каждого запроса свой результат
    if (strpos($path, '/search/') === 0) {
        return '';
    }

    // /catalog/index.php и /index.php - это те же /catalog/ и /
    $path = preg_replace('~/index\.php$~', '/', $path);

    $keep = [];

    foreach ($_GET as $name => $value) {
        // Постраничную навигацию оставляем, остальное отбрасываем
        if (is_string($value) && strpos((string)$name, 'PAGEN_') === 0) {
            $keep[(string)$name] = $value;
        }
    }

    ksort($keep);

    $query = empty($keep) ? '' : '?' . http_build_query($keep);

    return 'https://' . SITE_SERVER_NAME . $path . $query;
}

/**
 * Абсолютный адрес текущей страницы без параметров запроса
 *
 * Нужен для rel="prev"/"next": раньше они собирались из HTTP_HOST и
 * REDIRECT_URL по http, хотя сайт работает только по https
 *
 * @return string
 */
function room_page_url(): string
{
    if (!defined('SITE_SERVER_NAME') || SITE_SERVER_NAME === '') {
        return '';
    }

    global $APPLICATION;

    $path = preg_replace('~/index\.php$~', '/', (string)$APPLICATION->GetCurPage(false));

    return 'https://' . SITE_SERVER_NAME . $path;
}

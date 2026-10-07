<?php

/**
 * Разделы каталога с сопутствующими товарами
 *
 * 21 - Montážny materiál, 50 - Šnúry a lana, 51 - Siete na lopty.
 * Из них собирается подборка для товаров, у которых не заполнен кроссейл
 */
const ROOM_ACCESSORY_SECTIONS = [21, 50, 51];

/**
 * Подборка товаров для блока «Súvisiace produkty», когда связи не заданы
 *
 * Сначала шаблон брал три случайных товара раздела, потом соседей по ID.
 * И то и другое показывало такие же сетки других размеров - то есть замену,
 * а не дополнение. Теперь подставляем крепёж, шнуры и сетки для переноски:
 * это и есть сопутствующие товары.
 *
 * Подборка сдвигается по ID товара, чтобы на разных карточках стояли
 * разные позиции, но у одной карточки всегда одни и те же.
 *
 * Если в этих разделах нет активных товаров, возвращается пусто - шаблон
 * тогда не рисует блок вовсе
 *
 * @param array $product элемент из $arResult шаблона catalog.element
 * @param int $limit сколько товаров нужно
 * @return array ID товаров, пустой массив - показывать нечего
 */
function room_related_ids(array $product, int $limit = 3): array
{
    // Столько сопутствующих товаров держим в обойме для сдвига подборки
    $poolSize = 60;

    $iblockId = (int)($product['IBLOCK_ID'] ?? 0);
    $currentId = (int)($product['ID'] ?? 0);

    if ($iblockId <= 0 || $currentId <= 0 || $limit <= 0) {
        return [];
    }

    $pool = room_catalog_ids([
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'CHECK_PERMISSIONS' => 'N',
        '!ID' => $currentId,
        'SECTION_ID' => ROOM_ACCESSORY_SECTIONS,
        'INCLUDE_SUBSECTIONS' => 'Y',
    ], $poolSize);

    if (empty($pool)) {
        return [];
    }

    if (count($pool) <= $limit) {
        return $pool;
    }

    // Сдвиг по ID товара: у каждой карточки своя тройка, но неизменная
    $offset = $currentId % count($pool);
    $rotated = array_merge(array_slice($pool, $offset), array_slice($pool, 0, $offset));

    return array_slice($rotated, 0, $limit);
}

/**
 * Оставляет только те ID, которым соответствуют активные товары
 *
 * Связь может вести на отключённый или удалённый товар - тогда компонент
 * вернёт пусто, а шаблон нарисует заголовок над пустым блоком
 *
 * @param array $ids
 * @param int $iblockId
 * @return array
 */
function room_active_ids(array $ids, int $iblockId): array
{
    $ids = array_values(array_filter(array_map('intval', $ids)));

    if (empty($ids)) {
        return [];
    }

    $alive = room_catalog_ids([
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'CHECK_PERMISSIONS' => 'N',
        'ID' => $ids,
    ], count($ids));

    // Возвращаем в том порядке, в котором связи перечислены в карточке
    return array_values(array_intersect($ids, $alive));
}

/**
 * Список ID товаров по фильтру
 *
 * @param array $filter
 * @param int $limit
 * @return array
 */
function room_catalog_ids(array $filter, int $limit): array
{
    if ($limit <= 0) {
        return [];
    }

    $ids = [];

    $rs = \CIBlockElement::GetList(
        ['ID' => 'ASC'],
        $filter,
        false,
        ['nTopCount' => $limit],
        ['ID']
    );

    while ($row = $rs->Fetch()) {
        $ids[] = (int)$row['ID'];
    }

    return $ids;
}

/**
 * Подпись для изображения товара (атрибут alt)
 *
 * Сначала берётся SEO-поле «Alt изображения» из карточки товара, если его
 * заполнили в админке, иначе - название товара. Пустой alt оставляем только
 * у декоративных картинок вроде иконок интерфейса
 *
 * @param array $item элемент каталога из $arResult компонента
 * @param string $type PREVIEW для карточки в списке, DETAIL для галереи
 * @return string готовая к выводу строка, экранирование на вызывающей стороне
 */
function room_picture_alt(array $item, string $type = 'DETAIL'): string
{
    $keys = $type === 'PREVIEW'
        ? ['ELEMENT_PREVIEW_PICTURE_FILE_ALT', 'ELEMENT_DETAIL_PICTURE_FILE_ALT']
        : ['ELEMENT_DETAIL_PICTURE_FILE_ALT', 'ELEMENT_PREVIEW_PICTURE_FILE_ALT'];

    foreach ($keys as $key) {
        $alt = trim((string)($item['IPROPERTY_VALUES'][$key] ?? ''));

        if ($alt !== '') {
            return $alt;
        }
    }

    // ~NAME - без html-экранирования, его добавит шаблон
    return trim((string)($item['~NAME'] ?? $item['NAME'] ?? ''));
}

/**
 * Значение «Доступность на складе», при котором товар нельзя заказать.
 * Карточка в этом случае пишет «Dočasne nedostupné» вместо кнопки
 */
const ROOM_UNAVAILABLE_ENUM_ID = 20;

/**
 * Разметка товара schema.org/Product для поисковиков и Google Merchant
 *
 * Merchant сверяет цену и наличие в фиде со страницей товара. Без разметки
 * ему приходится угадывать их по вёрстке, и товары отклоняются из-за
 * «несоответствия цены». Цена и наличие здесь ровно те, что видит
 * покупатель: «Cena s DPH» первого предложения и кнопка «Do košíka»
 * либо «Dočasne nedostupné».
 *
 * Сетки продаются за m²: цена указывается с пометкой «за 1 m²», иначе
 * Google решит, что столько стоит всё изделие.
 *
 * @param array $item $arResult шаблона catalog.element
 * @param bool $perSquareMeter цена на карточке указана за m²
 * @return array пусто, если цены нет - без цены разметка Google не нужна
 */
function room_product_schema(array $item, bool $perSquareMeter): array
{
    $offer = $item['OFFERS'][0] ?? null;
    $price = (float)($offer ? ($offer['MIN_PRICE']['DISCOUNT_VALUE'] ?? 0) : ($item['MIN_PRICE']['DISCOUNT_VALUE'] ?? 0));

    if ($price <= 0) {
        return [];
    }

    $host = 'https://' . (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME ? SITE_SERVER_NAME : 'luxsol.sk');

    // Фото: основное товара, затем фото цветов. Без повторов, максимум 10
    $images = [];
    $pictures = [$item['DETAIL_PICTURE'] ?? null, $item['PREVIEW_PICTURE'] ?? null];
    foreach ((array)($item['OFFERS'] ?? []) as $o) {
        $pictures[] = $o['PREVIEW_PICTURE'] ?? null;
    }
    foreach ($pictures as $picture) {
        if (!empty($picture['SRC'])) {
            $images[$host . $picture['SRC']] = true;
        }
    }
    $images = array_slice(array_keys($images), 0, 10);

    $description = (string)($item['~PREVIEW_TEXT'] ?? '') !== '' ? $item['~PREVIEW_TEXT'] : (string)($item['~DETAIL_TEXT'] ?? '');
    $description = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if (mb_strlen($description) > 1000) {
        $description = rtrim(mb_substr($description, 0, 997)) . '…';
    }

    $sku = trim((string)($item['PROPERTIES']['ARTICLE']['VALUE'] ?? ''));
    if ($sku === '' && $offer) {
        $sku = trim((string)($offer['ARTICLE'] ?? $offer['ARTICUL'] ?? ''));
    }

    $available = (int)($item['PROPERTIES']['PROD_ACCESS']['VALUE_ENUM_ID'] ?? 0) !== ROOM_UNAVAILABLE_ENUM_ID;
    $url = $host . ($item['DETAIL_PAGE_URL'] ?? '');

    $offerSchema = [
        '@type' => 'Offer',
        'url' => $url,
        'price' => round($price, 2),
        'priceCurrency' => 'EUR',
        'availability' => 'https://schema.org/' . ($available ? 'InStock' : 'OutOfStock'),
        'itemCondition' => 'https://schema.org/NewCondition',
    ];

    if ($perSquareMeter) {
        $offerSchema['priceSpecification'] = [
            '@type' => 'UnitPriceSpecification',
            'price' => round($price, 2),
            'priceCurrency' => 'EUR',
            'referenceQuantity' => [
                '@type' => 'QuantitativeValue',
                'value' => 1,
                'unitCode' => 'MTK', // m² по справочнику UN/CEFACT
            ],
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => trim((string)($item['~NAME'] ?? $item['NAME'] ?? '')),
        'url' => $url,
    ];
    if ($images) {
        $schema['image'] = $images;
    }
    if ($description !== '') {
        $schema['description'] = $description;
    }
    if ($sku !== '') {
        $schema['sku'] = $sku;
    }
    $schema['offers'] = $offerSchema;

    return $schema;
}

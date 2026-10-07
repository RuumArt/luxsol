<?php

namespace Room\Tools;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use CCatalogProduct;
use CCatalogSku;
use CFile;
use CIBlockElement;
use CIBlockSection;
use Room\Delivery\DpdHandler;
use Room\Delivery\PacketaHandler;
use Room\Delivery\Tariff;
use Room\Delivery\ToptransHandler;

/**
 * Фид товаров для Google Merchant Center
 *
 * Раньше товары в Merchant вносили руками, и цены там отставали от сайта
 * на годы. Фид Merchant забирает сам раз в сутки, поэтому цена, наличие
 * и доставка в рекламе всегда те же, что видит покупатель.
 *
 * Один товар сайта - одна позиция фида. Цена, как на карточке, берётся у
 * первого цвета (торговое предложение с наименьшей сортировкой). Цвета
 * отдельными позициями не выгружаем: у карточки один адрес, и Google
 * сверял бы цену каждого цвета с ценой первого.
 *
 * Адрес фида: https://luxsol.sk/feeds/google-merchant.php
 * Проверка из консоли: php local/tools/merchant_feed.php
 */
class MerchantFeed
{
    const IBLOCK_ID = 1;

    /**
     * Цена для группы «Все пользователи» - её видит покупатель без входа
     */
    const PRICE_USER_GROUP = 2;

    /**
     * Разделы, где цена указана за m² (та же проверка, что в карточке товара)
     */
    const PER_M2_SECTIONS = [1, 46];

    const COUNTRY = 'SK';
    const CURRENCY = 'EUR';

    /**
     * Срок в пути после отгрузки, рабочих дней. Packeta и DPD по Словакии
     */
    const TRANSIT_DAYS = [1, 3];

    /**
     * Срок доставки, если на карточке не удалось его разобрать.
     * У большинства товаров на сайте «dodacia lehota 3 - 5 pracovných dní»
     */
    const DEFAULT_DELIVERY_DAYS = [3, 5];

    /**
     * Ярлык исключения из правил возврата в Merchant. Всё, что режут по
     * размерам покупателя (сетки за m², шнуры, тросы), по VOP п. 6.2 без
     * брака не возвращают. Само исключение заведено в Merchant под этим ярлыком
     */
    const MADE_TO_MEASURE_RETURN_LABEL = 'na-mieru';

    /**
     * Разделы отрезного товара: Šnúry a lana. Плюс всё, что продаётся
     * за метр (MEASURE_METER_IDS), - например, трос из Montážny materiál
     */
    const CUT_TO_LENGTH_SECTIONS = [50];

    /**
     * Единицы измерения «метр» и «погонный метр» в справочнике каталога
     */
    const MEASURE_METER_IDS = [7, 9];

    /**
     * Разделы, которые не рекламируем: Montážny materiál и Šnúry a lana.
     * Это крепёж и шнуры по 0,05-2,50 €: клик в рекламе дороже товара.
     * В бесплатных показах Google они остаются.
     *
     * Не ROOM_ACCESSORY_SECTIONS: там ещё «Siete na lopty» - это спортивные
     * сетки, их рекламировать нужно
     */
    const NO_ADS_SECTIONS = [21, 50];

    /**
     * Куда не пускать товары из NO_ADS_SECTIONS: товарные объявления
     * (в том числе Performance Max) и медийная реклама
     */
    const NO_ADS_DESTINATIONS = ['Shopping_ads', 'Display_ads'];

    const CACHE_TTL = 3600;
    const CACHE_DIR = '/room/merchant_feed';

    /**
     * Ограничения Google на длину полей
     */
    const TITLE_MAX = 150;
    const DESCRIPTION_MAX = 5000;
    const EXTRA_IMAGES_MAX = 10;

    /**
     * Готовый XML фида, кешируется на час
     *
     * @return string
     */
    public static function xml(): string
    {
        $cache = Cache::createInstance();

        if ($cache->initCache(self::CACHE_TTL, 'feed', self::CACHE_DIR)) {
            return (string)$cache->getVars();
        }

        $xml = self::render(self::collect()['items']);

        $cache->startDataCache();
        $cache->endDataCache($xml);

        return $xml;
    }

    /**
     * Позиции фида и товары, которые в него не попали, с причиной
     *
     * @return array ['items' => [...], 'skipped' => [[id, name, reason], ...]]
     */
    public static function collect(): array
    {
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $host = 'https://' . (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME ? SITE_SERVER_NAME : 'luxsol.sk');

        $products = [];

        $rs = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => self::IBLOCK_ID,
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'SECTION_GLOBAL_ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_TEXT_TYPE']
        );

        while ($ob = $rs->GetNextElement()) {
            $fields = $ob->GetFields();
            $fields['PROPERTIES'] = $ob->GetProperties();
            $products[(int)$fields['ID']] = $fields;
        }

        if (!$products) {
            return ['items' => [], 'skipped' => []];
        }

        $offers = self::offers(array_keys($products));
        $sections = self::sections();

        // Артикул - это item_id в покупках GA4 и ID товаров, которые раньше
        // вносили в Merchant руками: так статистика Ads по товару продолжится.
        // Пустой или повторяющийся артикул заменяем ID товара
        $articleCount = [];
        foreach ($products as $p) {
            $article = self::article($p);
            if ($article !== '') {
                $articleCount[$article] = ($articleCount[$article] ?? 0) + 1;
            }
        }

        $items = [];
        $skipped = [];

        foreach ($products as $id => $p) {
            $name = trim(html_entity_decode((string)$p['~NAME'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $productOffers = $offers[$id] ?? [];
            $first = $productOffers[0] ?? null;

            $price = self::price($first ? (int)$first['ID'] : $id);
            if ($price <= 0) {
                $skipped[] = [$id, $name, 'нет цены'];
                continue;
            }

            $images = self::images($p, $productOffers, $host);
            if (!$images) {
                $skipped[] = [$id, $name, 'нет фото'];
                continue;
            }

            $chain = $sections[(int)$p['IBLOCK_SECTION_ID']] ?? [];
            $sectionIds = self::productSectionIds($id, $sections);
            $perM2 = (bool)array_intersect(self::PER_M2_SECTIONS, $sectionIds);

            $article = self::article($p);
            $feedId = ($article !== '' && $articleCount[$article] === 1) ? $article : (string)$id;

            $access = $p['PROPERTIES']['PROD_ACCESS'] ?? [];
            $available = (int)($access['VALUE_ENUM_ID'] ?? 0) !== ROOM_UNAVAILABLE_ENUM_ID;

            $catalogProduct = CCatalogProduct::GetByID($first ? (int)$first['ID'] : $id) ?: [];
            $weightKg = (float)($catalogProduct['WEIGHT'] ?? 0) / 1000;

            $cutToLength = $perM2
                || array_intersect(self::CUT_TO_LENGTH_SECTIONS, $sectionIds)
                || in_array((int)($catalogProduct['MEASURE'] ?? 0), self::MEASURE_METER_IDS, true);

            $item = [
                'id' => $feedId,
                'title' => self::cut($name, self::TITLE_MAX),
                'description' => self::description($p, $name),
                'link' => $host . $p['DETAIL_PAGE_URL'],
                'image_link' => $images[0],
                'additional_image_link' => array_slice($images, 1, self::EXTRA_IMAGES_MAX),
                'availability' => $available ? 'in_stock' : 'out_of_stock',
                'price' => self::money($price),
                'condition' => 'new',
                'brand' => 'Luxsol',
                'identifier_exists' => 'no',
                'product_type' => implode(' > ', array_column($chain, 'NAME')),
                'custom_label_0' => (string)($chain[0]['NAME'] ?? ''),
                'shipping' => self::shipping($weightKg),
                'handling' => self::handlingDays((string)($access['VALUE'] ?? '')),
                'site_id' => $id,
            ];

            if ($weightKg > 0) {
                $item['shipping_weight'] = rtrim(rtrim(number_format($weightKg, 3, '.', ''), '0'), '.') . ' kg';
            }

            // Цена сетки указана за 1 m²: Google покажет её как цену за единицу
            if ($perM2) {
                $item['unit_pricing_measure'] = '1 sqm';
                $item['unit_pricing_base_measure'] = '1 sqm';
            }

            // Отрезанное по размеру покупателя - свои правила возврата
            if ($cutToLength) {
                $item['return_policy_label'] = self::MADE_TO_MEASURE_RETURN_LABEL;
            }

            if (array_intersect(self::NO_ADS_SECTIONS, $sectionIds)) {
                $item['excluded_destination'] = self::NO_ADS_DESTINATIONS;
            }

            $items[] = $item;
        }

        return ['items' => $items, 'skipped' => $skipped];
    }

    /**
     * XML в формате RSS 2.0 с пространством имён g:
     *
     * @param array $items
     * @return string
     */
    public static function render(array $items): string
    {
        $out = [];
        $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $out[] = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $out[] = '<channel>';
        $out[] = '<title>Luxsol.sk</title>';
        $out[] = '<link>https://luxsol.sk/</link>';
        $out[] = '<description>Luxsol.sk - produkty pre Google Merchant Center</description>';

        foreach ($items as $item) {
            $out[] = '<item>';

            foreach (['id', 'title', 'description', 'link', 'image_link'] as $key) {
                $out[] = self::tag($key, $item[$key]);
            }
            foreach ($item['additional_image_link'] as $image) {
                $out[] = self::tag('additional_image_link', $image);
            }
            foreach (['availability', 'price', 'unit_pricing_measure', 'unit_pricing_base_measure', 'condition', 'brand', 'identifier_exists', 'product_type', 'custom_label_0', 'shipping_weight', 'return_policy_label'] as $key) {
                if (isset($item[$key]) && $item[$key] !== '') {
                    $out[] = self::tag($key, $item[$key]);
                }
            }
            foreach ($item['excluded_destination'] ?? [] as $destination) {
                $out[] = self::tag('excluded_destination', $destination);
            }

            if ($item['shipping']) {
                $out[] = '<g:shipping>'
                    . self::tag('country', self::COUNTRY)
                    . self::tag('service', $item['shipping']['service'])
                    . self::tag('price', self::money($item['shipping']['price']))
                    . self::tag('min_transit_time', self::TRANSIT_DAYS[0])
                    . self::tag('max_transit_time', self::TRANSIT_DAYS[1])
                    . '</g:shipping>';
            }

            $out[] = self::tag('min_handling_time', $item['handling'][0]);
            $out[] = self::tag('max_handling_time', $item['handling'][1]);

            $out[] = '</item>';
        }

        $out[] = '</channel>';
        $out[] = '</rss>';

        return implode("\n", $out) . "\n";
    }

    /**
     * Срок отгрузки из надписи на карточке
     *
     * Google считает срок доставки как отгрузку плюс время в пути, поэтому
     * из срока на сайте вычитаем время в пути. «dodacia lehota 3 - 5
     * pracovných dní» при пути 1-3 дня даёт отгрузку 2 дня, «do 10» - от 2
     * до 7. Надпись разбирается как текст: если клиент заведёт новый вариант
     * срока, фид подхватит его без правки кода
     *
     * @param string $text
     * @return array [мин, макс] рабочих дней
     */
    public static function handlingDays(string $text): array
    {
        [$min, $max] = self::DEFAULT_DELIVERY_DAYS;

        if (preg_match('/(\d+)\s*-\s*(\d+)/u', $text, $m)) {
            [$min, $max] = [(int)$m[1], (int)$m[2]];
        } elseif (preg_match('/do\s+(\d+)/iu', $text, $m)) {
            $max = (int)$m[1];
        }

        $handlingMin = max(0, $min - self::TRANSIT_DAYS[0]);
        $handlingMax = max($handlingMin, $max - self::TRANSIT_DAYS[1]);

        return [$handlingMin, $handlingMax];
    }

    /**
     * Самая дешёвая доставка одной штуки (или 1 m² сетки) теми же
     * тарифами, что считает корзина: Packeta до своего предела веса,
     * тяжелее - DPD, неделимый кусок тяжелее мешка DPD - Toptrans
     *
     * @param float $weightKg
     * @return array ['service' => string, 'price' => float]
     */
    public static function shipping(float $weightKg): array
    {
        $packetaMax = Tariff::number(Tariff::configOf(PacketaHandler::class), 'MAX_WEIGHT', PacketaHandler::MAX_WEIGHT);

        if ($weightKg <= $packetaMax + 0.000001) {
            return ['service' => 'Packeta', 'price' => PacketaHandler::priceForWeight($weightKg)];
        }

        if ($weightKg <= DpdHandler::maxBagWeight() + 0.000001) {
            return ['service' => 'DPD', 'price' => DpdHandler::priceForBag($weightKg)];
        }

        return ['service' => 'Toptrans', 'price' => ToptransHandler::priceForWeight($weightKg)];
    }

    /**
     * Активные торговые предложения в порядке карточки: сортировка по
     * возрастанию, при равной - сначала новые (как OFFERS_SORT_* в каталоге)
     *
     * @param array $productIds
     * @return array [ID товара => [предложение, ...]]
     */
    private static function offers(array $productIds): array
    {
        $list = CCatalogSku::getOffersList(
            $productIds,
            self::IBLOCK_ID,
            ['ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'],
            ['ID', 'SORT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
            ['CODE' => ['PICTURE']]
        );

        $result = [];

        foreach ((array)$list as $productId => $offers) {
            $offers = array_values($offers);
            usort($offers, static function (array $a, array $b) {
                return [(int)$a['SORT'], -(int)$a['ID']] <=> [(int)$b['SORT'], -(int)$b['ID']];
            });
            $result[(int)$productId] = $offers;
        }

        return $result;
    }

    /**
     * Цена с НДС и скидками, как «Cena s DPH» на карточке
     *
     * @param int $productId товар или торговое предложение
     * @return float
     */
    private static function price(int $productId): float
    {
        $optimal = CCatalogProduct::GetOptimalPrice($productId, 1, [self::PRICE_USER_GROUP], 'N', [], SITE_ID ?: 's1');

        return round((float)($optimal['RESULT_PRICE']['DISCOUNT_PRICE'] ?? 0), 2);
    }

    /**
     * Фото в порядке галереи карточки: фото первого цвета, анонс товара,
     * детальная, затем дополнительные фото цвета и товара
     *
     * @param array $product
     * @param array $offers
     * @param string $host
     * @return array абсолютные адреса без повторов
     */
    private static function images(array $product, array $offers, string $host): array
    {
        $first = $offers[0] ?? [];

        $ids = [
            $first['PREVIEW_PICTURE'] ?? null,
            $product['PREVIEW_PICTURE'] ?? null,
            $product['DETAIL_PICTURE'] ?? null,
            $first['DETAIL_PICTURE'] ?? null,
        ];
        foreach ((array)($first['PROPERTIES']['PICTURE']['VALUE'] ?? []) as $fileId) {
            $ids[] = $fileId;
        }
        foreach ((array)($product['PROPERTIES']['PHOTO']['VALUE'] ?? []) as $fileId) {
            $ids[] = $fileId;
        }

        $images = [];

        foreach ($ids as $fileId) {
            if (!$fileId) {
                continue;
            }
            $path = CFile::GetPath((int)$fileId);
            if ($path) {
                $images[$host . $path] = true;
            }
        }

        return array_keys($images);
    }

    /**
     * Описание: анонс или детальный текст. Если текста нет, собираем из
     * характеристик - пустое описание Google не принимает
     *
     * @param array $product
     * @param string $name
     * @return string
     */
    private static function description(array $product, string $name): string
    {
        foreach (['~PREVIEW_TEXT', '~DETAIL_TEXT'] as $key) {
            $text = self::plain((string)($product[$key] ?? ''));
            if ($text !== '') {
                return self::cut($text, self::DESCRIPTION_MAX);
            }
        }

        $props = $product['PROPERTIES'];
        $parts = [$name . '.'];

        $material = trim((string)($props['MATERIAL']['VALUE'] ?? ''));
        if ($material !== '') {
            $parts[] = 'Materiál: ' . $material . '.';
        }
        $thickness = trim((string)($props['DIAMETR']['VALUE'] ?? ''));
        if ($thickness !== '') {
            $parts[] = 'Hrúbka: ' . $thickness . ' mm.';
        }
        $mesh = trim((string)($props['RAZMER']['VALUE'] ?? ''));
        if ($mesh !== '') {
            $parts[] = 'Veľkosť oka: ' . $mesh . ' mm.';
        }
        $size = trim((string)($props['SIZE']['VALUE'] ?? ''));
        if ($size !== '') {
            $parts[] = 'Rozmery: ' . $size . '.';
        }

        return self::plain(implode(' ', $parts));
    }

    /**
     * Цепочки разделов от верхнего: [ID раздела => [[ID, NAME], ...]]
     *
     * @return array
     */
    private static function sections(): array
    {
        $all = [];
        $rs = CIBlockSection::GetList(
            ['LEFT_MARGIN' => 'ASC'],
            ['IBLOCK_ID' => self::IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID']
        );
        while ($s = $rs->Fetch()) {
            $all[(int)$s['ID']] = $s;
        }

        $chains = [];
        foreach ($all as $id => $s) {
            $chain = [];
            for ($cur = $id; $cur && isset($all[$cur]) && count($chain) < 10; $cur = (int)$all[$cur]['IBLOCK_SECTION_ID']) {
                array_unshift($chain, ['ID' => $cur, 'NAME' => trim($all[$cur]['NAME'])]);
            }
            $chains[$id] = $chain;
        }

        return $chains;
    }

    /**
     * Все разделы товара вместе с родителями: товар может лежать в
     * нескольких разделах, и цена за m² определяется по любому из них
     *
     * @param int $productId
     * @param array $sections
     * @return array
     */
    private static function productSectionIds(int $productId, array $sections): array
    {
        $ids = [];
        $rs = CIBlockElement::GetElementGroups($productId, true, ['ID']);
        while ($g = $rs->Fetch()) {
            foreach ($sections[(int)$g['ID']] ?? [] as $s) {
                $ids[] = (int)$s['ID'];
            }
        }

        return array_unique($ids);
    }

    /**
     * Артикул товара, а не цвета
     *
     * У части цветов свой артикул, и в заказ (а значит, и в GA4) уходит он.
     * Но товары в Merchant раньше вносили по артикулу товара: с ним совпадают
     * 36 из 41, в том числе самые кликабельные, и статистика Ads продолжается
     *
     * @param array $product
     * @return string
     */
    private static function article(array $product): string
    {
        return trim((string)($product['PROPERTIES']['ARTICLE']['VALUE'] ?? ''));
    }

    private static function money(float $value): string
    {
        return number_format($value, 2, '.', '') . ' ' . self::CURRENCY;
    }

    /**
     * HTML в одну строку обычного текста
     */
    private static function plain(string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function cut(string $text, int $max): string
    {
        return mb_strlen($text) > $max ? rtrim(mb_substr($text, 0, $max - 1)) . '…' : $text;
    }

    /**
     * Элемент g:… с экранированием и без символов, запрещённых в XML
     */
    private static function tag(string $name, $value): string
    {
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', (string)$value);

        return '<g:' . $name . '>' . htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</g:' . $name . '>';
    }
}

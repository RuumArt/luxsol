<?php

namespace Room\Tools;

use Bitrix\Main\Data\Cache;

/**
 * Google Merchant Center: прежний адрес, ID, формат XML и правила рекламы.
 * Общие данные и расчёт доставки находятся в CatalogFeedData.
 */
class MerchantFeed extends CatalogFeedData
{
    const MADE_TO_MEASURE_RETURN_LABEL = 'na-mieru';
    const NO_ADS_DESTINATIONS = ['Shopping_ads', 'Display_ads'];
    const CACHE_TTL = 3600;
    const CACHE_DIR = '/room/merchant_feed';

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

    public static function collect(): array
    {
        $feed = parent::collect();
        foreach ($feed['items'] as &$item) {
            $madeToMeasure = $item['made_to_measure'];
            $adsAllowed = $item['ads_allowed'];
            unset($item['made_to_measure'], $item['ads_allowed']);
            if ($madeToMeasure) {
                $item['return_policy_label'] = self::MADE_TO_MEASURE_RETURN_LABEL;
            }
            if (!$adsAllowed) {
                $item['excluded_destination'] = self::NO_ADS_DESTINATIONS;
            }
        }
        unset($item);

        return $feed;
    }

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
}

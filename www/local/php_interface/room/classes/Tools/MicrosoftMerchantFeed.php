<?php

namespace Room\Tools;

use Bitrix\Main\Data\Cache;
use RuntimeException;

/**
 * Microsoft Merchant Center, Google-compatible RSS 2.0.
 * Microsoft игнорирует excluded_destination: нерекламные позиции удаляем.
 */
class MicrosoftMerchantFeed extends CatalogFeedData
{
    const CACHE_TTL = 3600;
    const CACHE_DIR = '/room/microsoft_merchant_feed';

    public static function collect(): array
    {
        return self::select(parent::collect());
    }

    /** Отбор вынесен отдельно для проверки на том же наборе данных, что и Google. */
    public static function select(array $feed): array
    {
        $items = [];
        $skipped = $feed['skipped'];
        $ids = [];

        foreach ($feed['items'] as $item) {
            $reason = '';
            if (!$item['ads_allowed']) {
                $reason = 'раздел исключён из платной рекламы';
            } elseif ($item['id'] === '' || mb_strlen($item['id']) > 50) {
                $reason = 'ID пустой или длиннее 50 символов';
            } elseif (isset($ids[$item['id']])) {
                throw new RuntimeException('Duplicate Microsoft Merchant product ID');
            }

            if ($reason !== '') {
                $skipped[] = [$item['site_id'], $item['title'], $reason];
                continue;
            }

            $ids[$item['id']] = true;
            // Только поддерживаемые поля: Google-политики возврата и назначений
            // не должны попадать в Microsoft под видом работающих настроек.
            $items[] = array_intersect_key($item, array_flip([
                'id', 'title', 'description', 'link', 'image_link',
                'additional_image_link', 'availability', 'price', 'condition',
                'brand', 'identifier_exists', 'product_type', 'custom_label_0',
                'unit_pricing_measure', 'unit_pricing_base_measure', 'shipping',
                'site_id',
            ]));
        }

        return ['items' => $items, 'skipped' => $skipped];
    }

    public static function xml(): string
    {
        $cache = Cache::createInstance();
        if ($cache->initCache(self::CACHE_TTL, 'feed-v1', self::CACHE_DIR)) {
            return (string)$cache->getVars();
        }

        $xml = self::render(self::collect()['items']);
        $cache->startDataCache();
        $cache->endDataCache($xml);

        return $xml;
    }

    public static function render(array $items): string
    {
        // Ошибку/пустую выборку нельзя выдавать за успешное удаление каталога.
        if (!$items) {
            throw new RuntimeException('Microsoft Merchant feed has no eligible products');
        }

        $out = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">',
            '<channel>',
            '<title>Luxsol.sk</title>',
            '<link>https://luxsol.sk/</link>',
            '<description>Luxsol.sk - produkty pre Microsoft Merchant Center</description>',
        ];

        foreach ($items as $item) {
            $out[] = '<item>';
            foreach (['id', 'title', 'description', 'link', 'image_link'] as $key) {
                $out[] = self::tag($key, $item[$key]);
            }
            foreach ($item['additional_image_link'] as $image) {
                $out[] = self::tag('additional_image_link', $image);
            }
            // Значения Microsoft; Google использует вариант с подчёркиванием.
            $out[] = self::tag('availability', str_replace('_', ' ', $item['availability']));
            foreach (['price', 'condition', 'brand', 'identifier_exists', 'product_type', 'custom_label_0', 'unit_pricing_measure', 'unit_pricing_base_measure'] as $key) {
                if (isset($item[$key]) && $item[$key] !== '') {
                    $out[] = self::tag($key, $item[$key]);
                }
            }
            if ($item['shipping']) {
                $out[] = '<g:shipping>'
                    . self::tag('country', self::COUNTRY)
                    . self::tag('service', $item['shipping']['service'])
                    . self::tag('price', self::money($item['shipping']['price']))
                    . '</g:shipping>';
            }
            $out[] = '</item>';
        }

        $out[] = '</channel>';
        $out[] = '</rss>';

        return implode("\n", $out) . "\n";
    }
}

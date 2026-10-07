<?php

namespace Room\Delivery;

use Bitrix\Sale\Shipment;
use Room\Tools\Format;

/**
 * Раскладка заказа по мешкам для расчёта доставки
 *
 * В магазине два вида товаров: сетки, которые режут кусками, и обычные
 * штучные. Единица упаковки - отдельный кусок или отдельная штука: она
 * неделима и целиком лежит в одном мешке.
 *
 * Мешки набираются так, чтобы суммарный тариф был минимальным. Отдельного
 * правила "старайся уложиться в 20 кг" не нужно: тариф сам дорожает на
 * границе 20 кг, и минимизация это учитывает. Когда появится надбавка за
 * тяжёлые посылки, её достаточно добавить в функцию цены - логика раскладки
 * не изменится
 */
class Packer
{
    /**
     * До скольких кусков считаем точным перебором.
     * 3^10 вариантов - это доли секунды, дальше переходим на приближённый
     * расчёт, чтобы не тормозить оформление заказа
     */
    const EXACT_LIMIT = 10;

    /**
     * Вес, начиная с которого посылка считается тяжёлой.
     * Используется только приближённым расчётом как ориентир набивки
     */
    const LIGHT_BAG_WEIGHT = 20.0;

    /**
     * Веса всех кусков заказа в килограммах
     *
     * @param Shipment $shipment
     * @return array
     */
    public static function piecesFromShipment(Shipment $shipment): array
    {
        $collection = $shipment->getShipmentItemCollection();

        if (!$collection) {
            return [];
        }

        $pieces = [];

        foreach ($collection as $shipmentItem) {
            $basketItem = $shipmentItem->getBasketItem();

            if (!$basketItem) {
                continue;
            }

            $pieces = array_merge(
                $pieces,
                self::piecesFromBasketItem($basketItem, (float)$shipmentItem->getQuantity())
            );
        }

        rsort($pieces);

        return $pieces;
    }

    /**
     * Веса всех кусков корзины в килограммах
     *
     * Нужно там, где отгрузки ещё нет: отправка заказа в DPD идёт от корзины
     *
     * @param \Bitrix\Sale\BasketBase|iterable $basket
     * @return array
     */
    public static function piecesFromBasket($basket): array
    {
        if (!$basket) {
            return [];
        }

        $pieces = [];

        foreach ($basket as $basketItem) {
            $pieces = array_merge(
                $pieces,
                self::piecesFromBasketItem($basketItem, (float)$basketItem->getQuantity())
            );
        }

        rsort($pieces);

        return $pieces;
    }

    /**
     * Куски одной позиции корзины
     *
     * @param \Bitrix\Sale\BasketItemBase $basketItem
     * @param float $quantity
     * @return array
     */
    private static function piecesFromBasketItem($basketItem, float $quantity): array
    {
        if ($quantity <= 0) {
            return [];
        }

        $unitWeight = (float)$basketItem->getWeight();

        $sizes = '';
        $properties = $basketItem->getPropertyCollection();

        if ($properties) {
            $values = $properties->getPropertyValues();
            $sizes = (string)($values['SIZES_STR']['VALUE'] ?? '');
        }

        return $sizes !== ''
            ? self::piecesFromSizes($sizes, $unitWeight * $quantity)
            : self::piecesFromQuantity($unitWeight, $quantity);
    }

    /**
     * Куски сетки по строке размеров
     *
     * Вес позиции пропорционален площади (проверено на заказе 2770:
     * 556,8 м² при 33 360 г, то есть 60 г на м²), поэтому вес каждого
     * куска считаем по его доле площади
     *
     * @param string $sizes строка вида "33 × 8,5 m × 1 ks., 32,5 × 8,5 m × 1 ks."
     * @param float $totalWeightGrams вес всей позиции в граммах
     * @return array
     */
    private static function piecesFromSizes(string $sizes, float $totalWeightGrams): array
    {
        $parsed = Format::parseSizes($sizes);

        if (empty($parsed)) {
            return self::piecesFromQuantity($totalWeightGrams, 1);
        }

        $totalArea = 0.0;

        foreach ($parsed as $size) {
            $totalArea += (float)$size['height'] * (float)$size['width'] * (float)$size['count'];
        }

        if ($totalArea <= 0) {
            return self::piecesFromQuantity($totalWeightGrams, 1);
        }

        // Грамм на квадратный метр
        $density = $totalWeightGrams / $totalArea;

        $pieces = [];

        foreach ($parsed as $size) {
            $pieceWeight = (float)$size['height'] * (float)$size['width'] * $density / 1000;
            $count = max(1, (int)$size['count']);

            for ($i = 0; $i < $count; $i++) {
                $pieces[] = $pieceWeight;
            }
        }

        return $pieces;
    }

    /**
     * Штучный товар: каждая штука - отдельная единица упаковки
     *
     * @param float $unitWeightGrams
     * @param float $quantity
     * @return array
     */
    private static function piecesFromQuantity(float $unitWeightGrams, float $quantity): array
    {
        $pieces = [];
        $count = max(1, (int)ceil($quantity));

        for ($i = 0; $i < $count; $i++) {
            $pieces[] = $unitWeightGrams / 1000;
        }

        return $pieces;
    }

    /**
     * Раскладывает куски по мешкам с минимальной суммарной ценой
     *
     * @param array $pieces веса кусков в килограммах
     * @param float $maxBag предел веса одного мешка
     * @param callable $priceOf цена мешка по его весу
     * @return array|null ['bags' => [[вес, ...], ...], 'price' => float]
     *                    null - есть неделимый кусок тяжелее предела
     */
    public static function pack(array $pieces, float $maxBag, callable $priceOf): ?array
    {
        $pieces = array_values(array_filter($pieces, static function ($weight) {
            return $weight > 0;
        }));

        if (empty($pieces)) {
            return ['bags' => [], 'price' => 0.0];
        }

        foreach ($pieces as $piece) {
            if ($piece > $maxBag + 0.000001) {
                return null;
            }
        }

        return count($pieces) <= self::EXACT_LIMIT
            ? self::packExact($pieces, $maxBag, $priceOf)
            : self::packGreedy($pieces, $maxBag, $priceOf);
    }

    /**
     * Точный расчёт: динамика по подмножествам кусков
     *
     * cost[S] - минимальная цена доставки набора кусков S. Набор разбивается
     * на "первый мешок" и остаток, причём в первый мешок всегда попадает
     * самый младший кусок набора - так каждый вариант перебирается однажды
     *
     * @param array $pieces
     * @param float $maxBag
     * @param callable $priceOf
     * @return array|null
     */
    private static function packExact(array $pieces, float $maxBag, callable $priceOf): ?array
    {
        $count = count($pieces);
        $full = (1 << $count) - 1;

        // Вес каждого подмножества
        $weight = array_fill(0, $full + 1, 0.0);

        for ($set = 1; $set <= $full; $set++) {
            $low = $set & -$set;

            // Номер младшего куска набора: через log() ловить нечего,
            // целочисленный сдвиг надёжнее
            $index = 0;

            while ((1 << $index) !== $low) {
                $index++;
            }

            $weight[$set] = $weight[$set ^ $low] + $pieces[$index];
        }

        $cost = array_fill(0, $full + 1, INF);
        $choice = array_fill(0, $full + 1, 0);
        $cost[0] = 0.0;

        for ($set = 1; $set <= $full; $set++) {
            $low = $set & -$set;

            // Перебор подмножеств набора
            for ($bag = $set; $bag > 0; $bag = ($bag - 1) & $set) {
                if (!($bag & $low) || $weight[$bag] > $maxBag + 0.000001) {
                    continue;
                }

                $rest = $cost[$set ^ $bag];

                if ($rest === INF) {
                    continue;
                }

                $candidate = (float)$priceOf($weight[$bag]) + $rest;

                if ($candidate < $cost[$set]) {
                    $cost[$set] = $candidate;
                    $choice[$set] = $bag;
                }
            }
        }

        if ($cost[$full] === INF) {
            return null;
        }

        $bags = [];
        $set = $full;

        while ($set > 0) {
            $bag = $choice[$set];
            $current = [];

            for ($i = 0; $i < $count; $i++) {
                if ($bag & (1 << $i)) {
                    $current[] = $pieces[$i];
                }
            }

            $bags[] = $current;
            $set ^= $bag;
        }

        return ['bags' => $bags, 'price' => round($cost[$full], 2)];
    }

    /**
     * Приближённый расчёт для заказов с большим числом кусков
     *
     * Набиваем мешки по убыванию веса двумя способами - до лёгкого предела
     * и до полного - и берём тот, что дешевле
     *
     * @param array $pieces
     * @param float $maxBag
     * @param callable $priceOf
     * @return array
     */
    private static function packGreedy(array $pieces, float $maxBag, callable $priceOf): array
    {
        rsort($pieces);

        $best = null;

        foreach ([self::LIGHT_BAG_WEIGHT, $maxBag] as $capacity) {
            $bags = [];

            foreach ($pieces as $piece) {
                $placed = false;

                foreach ($bags as $index => $bag) {
                    if (array_sum($bag) + $piece <= $capacity + 0.000001) {
                        $bags[$index][] = $piece;
                        $placed = true;
                        break;
                    }
                }

                if (!$placed) {
                    // Кусок тяжелее выбранной набивки едет отдельным мешком
                    $bags[] = [$piece];
                }
            }

            $price = 0.0;

            foreach ($bags as $bag) {
                $price += (float)$priceOf(array_sum($bag));
            }

            if ($best === null || $price < $best['price']) {
                $best = ['bags' => $bags, 'price' => round($price, 2)];
            }
        }

        return $best;
    }
}

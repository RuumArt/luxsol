<?php

use Room\Tools\Format;

/**
 * Число в локальном формате: 2.5 -> "2,5", 2.00 -> "2"
 *
 * @param mixed $value
 * @param int|null $decimals Знаков после запятой; null - без незначащих нулей
 * @return string
 */
function room_number($value, ?int $decimals = null): string
{
    return Format::number($value, $decimals);
}

/**
 * Цена с валютой: 69.98 -> "69,98 €"
 *
 * @param mixed $value
 * @param string $currency
 * @return string
 */
function room_price($value, string $currency = Format::DEFAULT_CURRENCY): string
{
    return Format::price($value, $currency);
}

/**
 * Вес с единицей измерения: 1944 -> "1944 g"
 *
 * @param mixed $value
 * @param string $unit
 * @return string
 */
function room_weight($value, string $unit = 'g'): string
{
    return Format::weight($value, $unit);
}

/**
 * Размеры из свойства корзины: "2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks."
 *
 * @param string $sizesStr
 * @return string
 */
function room_sizes(string $sizesStr): string
{
    return Format::sizes($sizesStr);
}

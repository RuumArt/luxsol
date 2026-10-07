<?php

namespace Room\Tools;

use Bitrix\Main\Loader;

/**
 * Единое форматирование чисел, цен, веса и размеров по всему сайту
 * Разделитель дробной части - запятая (2,5), как принято в Словакии
 *
 * Использовать можно как через класс:
 *   \Room\Tools\Format::price($price)
 * так и через короткие глобальные функции:
 *   room_price($price)
 */
class Format
{
    // Разделитель дробной части
    const DECIMAL_SEPARATOR = ',';

    // Валюта по умолчанию
    const DEFAULT_CURRENCY = 'EUR';

    // Максимум знаков после запятой в режиме автоопределения
    const AUTO_DECIMALS = 3;

    /**
     * Число в локальном формате
     * Примеры: 2.5 -> "2,5", 2.00 -> "2", 21.6 -> "21,6", 1234.5 -> "1 234,5"
     *
     * @param mixed $value Число или строка с числом (точка и запятая равнозначны)
     * @param int|null $decimals Знаков после запятой; null - автоматически, без незначащих нулей
     * @param string $thousandsSep Разделитель разрядов
     * @return string
     */
    public static function number($value, ?int $decimals = null, string $thousandsSep = ''): string
    {
        $number = self::toFloat($value);

        if ($decimals !== null) {
            return number_format($number, $decimals, self::DECIMAL_SEPARATOR, $thousandsSep);
        }

        $formatted = number_format($number, self::AUTO_DECIMALS, self::DECIMAL_SEPARATOR, $thousandsSep);

        // "2,500" -> "2,5", "2,000" -> "2"; запятая не даёт срезать нули целой части
        $formatted = rtrim($formatted, '0');

        return rtrim($formatted, self::DECIMAL_SEPARATOR);
    }

    /**
     * Цена с валютой: 69.98 -> "69,98 €"
     * Символ валюты и его положение берутся из настроек Битрикса (Магазин - Валюты),
     * разделитель дробной части приводится к запятой в любом случае
     *
     * @param mixed $value
     * @param string $currency Код валюты
     * @return string
     */
    public static function price($value, string $currency = self::DEFAULT_CURRENCY): string
    {
        $number = self::toFloat($value);

        if (Loader::includeModule('currency')) {
            $formatted = (string) \CCurrencyLang::CurrencyFormat($number, $currency, true);

            // Запятая уже есть - формат валюты настроен верно, не трогаем
            // (иначе разделитель разрядов "1.234,56" превратился бы в "1,234,56")
            if (mb_strpos($formatted, self::DECIMAL_SEPARATOR) !== false) {
                return $formatted;
            }

            return self::decimalSeparator($formatted);
        }

        // Модуль валют недоступен - собираем строку сами
        return self::number($number, 2) . ' €';
    }

    /**
     * Цена без символа валюты: 69.98 -> "69,98"
     *
     * @param mixed $value
     * @param int $decimals
     * @return string
     */
    public static function decimal($value, int $decimals = 2): string
    {
        return self::number($value, $decimals);
    }

    /**
     * Вес с единицей измерения: 1944 -> "1944 g"
     *
     * @param mixed $value
     * @param string $unit Единица измерения
     * @return string
     */
    public static function weight($value, string $unit = 'g'): string
    {
        return trim(self::number($value) . ' ' . $unit);
    }

    /**
     * Приводит строку размеров к единому виду
     * На входе многострочное значение из PriceCalculator: "2.5x3.5mx1 ks.\n1.8x2.6mx2 ks."
     * На выходе одна строка: "2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks."
     *
     * Метод идемпотентен: уже отформатированная строка не меняется
     *
     * @param string $sizesStr Значение свойства корзины SIZES_STR
     * @param string $glue Разделитель между размерами
     * @return string
     */
    public static function sizes(string $sizesStr, string $glue = ', '): string
    {
        $sizes = self::parseSizes($sizesStr);

        if (empty($sizes)) {
            // Формат не распознан - хотя бы разделитель дробной части
            return self::decimalSeparator(trim($sizesStr));
        }

        $result = [];

        foreach ($sizes as $size) {
            $result[] = sprintf(
                '%s × %s m × %s ks.',
                self::number($size['height']),
                self::number($size['width']),
                self::number($size['count'])
            );
        }

        return implode($glue, $result);
    }

    /**
     * Размеры отдельными строками: ["2 × 2 m – 1 ks", "3 × 3 m – 3 ks"]
     * Нужно там, где каждый кусок показывается своей строкой (корзина, заказ)
     *
     * @param string $sizesStr Значение свойства корзины SIZES_STR
     * @param string $glue Разделитель между размером и количеством
     * @return array
     */
    public static function sizeLines(string $sizesStr, string $glue = ' – '): array
    {
        $lines = [];

        foreach (self::parseSizes($sizesStr) as $size) {
            $lines[] = sprintf(
                '%s × %s m%s%s ks',
                self::number($size['height']),
                self::number($size['width']),
                $glue,
                self::number($size['count'])
            );
        }

        return $lines;
    }

    /**
     * Разбирает строку размеров на отдельные позиции
     * Понимает и старый многострочный формат ("2.5x3.5mx1 ks.\n..."),
     * и текущий однострочный ("2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks.")
     *
     * @param string $sizesStr
     * @return array [['height' => float, 'width' => float, 'count' => float], ...]
     */
    public static function parseSizes(string $sizesStr): array
    {
        // Разделителем может прийти латинская x, кириллическая х или знак умножения
        $pattern = '/([\d.,]+)\s*[x\x{0445}\x{00D7}]\s*([\d.,]+)\s*m\s*[x\x{0445}\x{00D7}]\s*([\d.,]+)\s*ks\.?/iu';

        if (!preg_match_all($pattern, $sizesStr, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $sizes = [];

        foreach ($matches as $match) {
            $sizes[] = [
                'height' => self::toFloat($match[1]),
                'width' => self::toFloat($match[2]),
                'count' => self::toFloat($match[3]),
            ];
        }

        return $sizes;
    }

    /**
     * Форматирует один размер: "2.5x3.5mx1 ks." -> "2,5 × 3,5 m × 1 ks."
     *
     * @param string $line Одна строка размера
     * @return string
     */
    public static function sizeLine(string $line): string
    {
        // Разделителем может прийти латинская x, кириллическая х или знак умножения
        $pattern = '/^([\d.,]+)\s*[x\x{0445}\x{00D7}]\s*([\d.,]+)\s*m\s*[x\x{0445}\x{00D7}]\s*([\d.,]+)\s*(?:ks\.?)?$/iu';

        if (preg_match($pattern, $line, $matches)) {
            return sprintf(
                '%s × %s m × %s ks.',
                self::number($matches[1]),
                self::number($matches[2]),
                self::number($matches[3])
            );
        }

        // Формат не распознан - меняем только разделитель дробной части
        return self::decimalSeparator($line);
    }

    /**
     * Меняет точку на запятую в числах внутри произвольного текста
     * Полезно для готовых строк, формат которых заранее не известен
     *
     * @param string $text
     * @return string
     */
    public static function decimalSeparator(string $text): string
    {
        return (string) preg_replace('/(\d)\.(\d)/u', '$1' . self::DECIMAL_SEPARATOR . '$2', $text);
    }

    /**
     * Приводит значение к float, понимая и точку, и запятую
     *
     * @param mixed $value
     * @return float
     */
    private static function toFloat($value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        return (float) str_replace([' ', ','], ['', '.'], (string) $value);
    }
}

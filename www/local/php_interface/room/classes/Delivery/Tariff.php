<?php

namespace Room\Delivery;

use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\Services\Table as ServiceTable;

/**
 * Весовая шкала тарифа: разбор, поиск цены и чтение настроек службы
 *
 * Цены перевозчиков меняются несколько раз в год, и раньше их правил
 * разработчик прямо в коде. Теперь шкала лежит в настройках службы
 * доставки и редактируется в админке, а константы в обработчиках
 * остались значением по умолчанию: пока поле пустое, работает старая цена
 */
class Tariff
{
    /**
     * Разделитель строк шкалы в поле настроек
     */
    const ROW_SEPARATOR = ';';

    /**
     * Разбор шкалы из строки настроек
     *
     * Формат: "3=7; 5=8; 10=9" - до 3 кг 7 евро, до 5 кг 8 и так далее.
     * Дробная часть принимается и через точку, и через запятую: в Словакии
     * пишут "31,5", и заставлять менеджера помнить про точку незачем
     *
     * Любая ошибка разбора возвращает шкалу по умолчанию: неверно понятая
     * строка не должна обернуться бесплатной или заградительной доставкой
     *
     * @param string $text
     * @param array $default шкала из константы обработчика
     * @return array список [верхняя граница веса, цена]
     */
    public static function parse($text, array $default): array
    {
        $text = trim((string)$text);

        if ($text === '') {
            return $default;
        }

        $rows = [];

        foreach (explode(self::ROW_SEPARATOR, $text) as $row) {
            $row = trim($row);

            if ($row === '') {
                continue;
            }

            $parts = explode('=', $row);

            if (count($parts) !== 2) {
                return $default;
            }

            $limit = self::toFloat($parts[0]);
            $price = self::toFloat($parts[1]);

            if ($limit === null || $price === null || $limit <= 0 || $price < 0) {
                return $default;
            }

            $rows[] = [$limit, $price];
        }

        if (empty($rows)) {
            return $default;
        }

        // Строки могут быть записаны вразнобой, а поиск цены идёт по возрастанию
        usort($rows, static function (array $a, array $b) {
            return $a[0] <=> $b[0];
        });

        return $rows;
    }

    /**
     * Обратное преобразование: шкала в строку для поля настроек
     *
     * @param array $rows
     * @return string
     */
    public static function format(array $rows): string
    {
        $parts = [];

        foreach ($rows as [$limit, $price]) {
            $parts[] = self::trimZeros($limit) . '=' . self::trimZeros($price);
        }

        return implode(self::ROW_SEPARATOR . ' ', $parts);
    }

    /**
     * Цена по весу: первая строка, в которую вес укладывается
     *
     * Вес тяжелее последней строки оплачивается по последней: обработчики
     * и так не дают собрать посылку тяжелее своего предела, но расчёт
     * не должен давать ноль, если предел когда-нибудь разойдётся со шкалой
     *
     * @param array $rows
     * @param float $weightKg
     * @return float
     */
    public static function priceFor(array $rows, float $weightKg): float
    {
        foreach ($rows as [$limit, $price]) {
            // Допуск на дробный вес: 10,0000001 кг - это всё ещё 10 кг
            if ($weightKg <= $limit + 0.000001) {
                return (float)$price;
            }
        }

        $last = end($rows);

        return $last ? (float)$last[1] : 0.0;
    }

    /**
     * Настройки службы доставки по классу обработчика
     *
     * Нужны там, где объекта службы нет под рукой: сборка посылок DPD
     * и проверочные скрипты считают цену статически
     *
     * @param string $className
     * @return array содержимое раздела MAIN
     */
    public static function configOf(string $className): array
    {
        static $cache = [];

        if (isset($cache[$className])) {
            return $cache[$className];
        }

        $cache[$className] = [];

        if (!Loader::includeModule('sale')) {
            return $cache[$className];
        }

        // В базе имя класса записано с ведущей обратной чертой
        $row = ServiceTable::getList([
            'filter' => ['=CLASS_NAME' => '\\' . ltrim($className, '\\')],
            'select' => ['ID', 'CONFIG'],
            'limit' => 1,
        ])->fetch();

        if ($row && is_array($row['CONFIG'] ?? null) && is_array($row['CONFIG']['MAIN'] ?? null)) {
            $cache[$className] = $row['CONFIG']['MAIN'];
        }

        return $cache[$className];
    }

    /**
     * Число из настроек с откатом на значение по умолчанию
     *
     * @param array $config
     * @param string $key
     * @param float $default
     * @return float
     */
    public static function number(array $config, string $key, float $default): float
    {
        $value = self::toFloat($config[$key] ?? null);

        return ($value === null || $value < 0) ? $default : $value;
    }

    /**
     * @param mixed $value
     * @return float|null null - не число
     */
    private static function toFloat($value)
    {
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }

        $value = str_replace(',', '.', trim((string)$value));

        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (float)$value;
    }

    /**
     * 31.50 -> "31.5", 7.00 -> "7"
     *
     * @param float $number
     * @return string
     */
    private static function trimZeros(float $number): string
    {
        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }
}

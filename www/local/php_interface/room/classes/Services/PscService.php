<?php

namespace Room\Services;

use Bitrix\Main\Application;

/**
 * Справочник словацких почтовых индексов
 *
 * Данные берутся из официальных файлов Slovenská pošta (OBCE.xlsx,
 * ULICE.xlsx, POBoxy.xlsx) и заливаются в таблицу room_sk_psc
 * скриптом local/tools/import_psc.php
 *
 * Зачем отдельная таблица, а не файл: индексов около 1400, строк
 * справочника около 15 000, и по ним нужен быстрый поиск по префиксу
 * на каждое нажатие клавиши в оформлении заказа
 */
class PscService
{
    const TABLE = 'room_sk_psc';

    // Источник строки справочника
    const SRC_OBEC = 'O';    // OBCE.xlsx, лист "obce"
    const SRC_DETAIL = 'D';  // OBCE.xlsx, лист "detail SČ"
    const SRC_STREET = 'U';  // ULICE.xlsx
    const SRC_POBOX = 'P';   // POBoxy.xlsx, только специфические индексы

    /**
     * Источники, по которым курьер доставляет.
     * Индексы абонентских ящиков сюда не входят: DPD по ним не возит,
     * и предлагать их покупателю нельзя
     */
    const DELIVERABLE = [self::SRC_OBEC, self::SRC_DETAIL, self::SRC_STREET];

    /**
     * Замена словацкой диакритики для поиска без учёта надстрочных знаков
     */
    const TRANSLIT = [
        'á' => 'a', 'ä' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e',
        'í' => 'i', 'ĺ' => 'l', 'ľ' => 'l', 'ň' => 'n', 'ó' => 'o',
        'ô' => 'o', 'ŕ' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u',
        'ý' => 'y', 'ž' => 'z', 'ě' => 'e', 'ř' => 'r', 'ů' => 'u',
    ];

    /**
     * Индекс в виде пяти цифр: "985 13" -> "98513"
     *
     * @param string $zip
     * @return string
     */
    public static function normalize(string $zip): string
    {
        return (string)preg_replace('/\D/', '', $zip);
    }

    /**
     * Ключ для поиска по названию: без диакритики, в нижнем регистре
     *
     * @param string $value
     * @return string
     */
    public static function searchKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, self::TRANSLIT);

        return (string)preg_replace('/\s+/u', ' ', $value);
    }

    /**
     * Залит ли справочник. Пока импорт не выполнен, сайт должен работать
     * как раньше, а не падать на отсутствующей таблице
     *
     * @return bool
     */
    public static function isReady(): bool
    {
        static $ready = null;

        if ($ready === null) {
            $ready = Application::getConnection()->isTableExists(self::TABLE);
        }

        return $ready;
    }

    /**
     * Подсказки по началу индекса: "985" -> [['value' => '98513', 'label' => 'Ábelová']]
     *
     * Один индекс может принадлежать нескольким населённым пунктам
     * (максимум в справочнике - 14), поэтому возвращается список
     *
     * @param string $term то, что набрал покупатель
     * @param int $limit
     * @return array
     */
    public static function suggest(string $term, int $limit = 15): array
    {
        $digits = self::normalize($term);

        if ($digits === '' || !self::isReady()) {
            return [];
        }

        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        $sql = 'SELECT PSC, OBEC FROM ' . self::TABLE
            . " WHERE PSC LIKE '" . $helper->forSql($digits) . "%'"
            . ' AND SRC IN (' . self::deliverableSql() . ')'
            . ' GROUP BY PSC, OBEC'
            . ' ORDER BY PSC ASC, OBEC ASC'
            . ' LIMIT ' . (int)$limit;

        $rows = [];

        $rs = $connection->query($sql);

        while ($row = $rs->fetch()) {
            $rows[] = [
                // value уходит в поле индекса, label - в поле города
                'value' => $row['PSC'],
                'label' => $row['OBEC'],
                'psc' => self::format($row['PSC']),
            ];
        }

        return $rows;
    }

    /**
     * Населённые пункты с таким индексом
     *
     * @param string $zip
     * @return array
     */
    public static function getCities(string $zip): array
    {
        $digits = self::normalize($zip);

        if (strlen($digits) !== 5 || !self::isReady()) {
            return [];
        }

        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        $sql = 'SELECT DISTINCT OBEC FROM ' . self::TABLE
            . " WHERE PSC = '" . $helper->forSql($digits) . "'"
            . ' AND SRC IN (' . self::deliverableSql() . ')'
            . ' ORDER BY OBEC ASC';

        $cities = [];

        $rs = $connection->query($sql);

        while ($row = $rs->fetch()) {
            $cities[] = $row['OBEC'];
        }

        return $cities;
    }

    /**
     * Есть ли такой индекс в реестре пошты как территориальный
     *
     * @param string $zip
     * @return bool
     */
    public static function exists(string $zip): bool
    {
        return !empty(self::getCities($zip));
    }

    /**
     * Индекс абонентского ящика: в реестре есть, но курьер туда не доставляет
     *
     * @param string $zip
     * @return bool
     */
    public static function isPoBox(string $zip): bool
    {
        $digits = self::normalize($zip);

        if (strlen($digits) !== 5 || !self::isReady()) {
            return false;
        }

        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        $row = $connection->query(
            'SELECT ID FROM ' . self::TABLE
            . " WHERE PSC = '" . $helper->forSql($digits) . "'"
            . " AND SRC = '" . self::SRC_POBOX . "' LIMIT 1"
        )->fetch();

        return !empty($row);
    }

    /**
     * Соответствует ли город индексу
     *
     * Сравнение без диакритики и регистра: покупатели пишут "Kosice"
     * не реже, чем "Košice"
     *
     * @param string $zip
     * @param string $city
     * @return bool
     */
    public static function matchesCity(string $zip, string $city): bool
    {
        $key = self::searchKey($city);

        if ($key === '') {
            return true;
        }

        foreach (self::getCities($zip) as $known) {
            if (self::searchKey($known) === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Индекс в привычном виде: "98513" -> "985 13"
     *
     * @param string $zip
     * @return string
     */
    public static function format(string $zip): string
    {
        $digits = self::normalize($zip);

        return strlen($digits) === 5
            ? substr($digits, 0, 3) . ' ' . substr($digits, 3)
            : $digits;
    }

    /**
     * Сколько строк в справочнике
     *
     * @return int
     */
    public static function count(): int
    {
        if (!self::isReady()) {
            return 0;
        }

        $row = Application::getConnection()
            ->query('SELECT COUNT(*) AS CNT FROM ' . self::TABLE)
            ->fetch();

        return (int)($row['CNT'] ?? 0);
    }

    /**
     * Список источников доставки для подстановки в SQL
     *
     * @return string
     */
    private static function deliverableSql(): string
    {
        return "'" . implode("','", self::DELIVERABLE) . "'";
    }
}

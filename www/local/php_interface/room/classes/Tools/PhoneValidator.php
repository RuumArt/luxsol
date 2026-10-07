<?php

namespace Room\Tools;

/**
 * Класс для валидации и нормализации номеров телефонов
 */
class PhoneValidator
{
    /**
     * Паттерны для проверки номеров телефонов разных стран
     * Формат: код страны без +, затем паттерн для остальной части номера
     */
    private static $countryPatterns = [
        '/^43[0-9]{8,13}$/',                    // Austria
        '/^9710?[2-7,9]\d{8}$/',               // UAE
        '/^32[0-9]{9,10}$/',                   // Belgium
        '/^421[0-9]{9}$/',                     // Slovakia
        '/^359(2\d{7}|8\d{8}|[13-79]\d{7,8})$/', // Bulgaria
        '/^385[0-9]{8,9}$/',                  // Croatia
        '/^420[0-9]{9}$/',                    // Czech Republic
        '/^45[0-9]{8}$/',                     // Denmark
        '/^372[0-9]{7,8}$/',                 // Estonia
        '/^358[0-9]{7,10}$/',                // Finland
        '/^33[0-9]{9}$/',                     // France
        '/^49[0-9]{7,12}$/',                  // Germany
        '/^30[0-9]{10}$/',                    // Greece
        '/^36[0-9]{8,9}$/',                  // Hungary
        '/^353[0-9]{8,9}$/',                 // Ireland
        '/^39[0-9]{8,12}$/',                 // Italy
        '/^3712[0-9]{7}$/',                  // Latvia
        '/^423[0-9]{7,9}$/',                 // Liechtenstein
        '/^370[0-9]{8,9}$/',                 // Lithuania
        '/^352[0-9]{4,11}$/',                // Luxembourg
        '/^356[0-9]{8}$/',                    // Malta
        '/^382[0-9]{6,8}$/',                 // Montenegro
        '/^31[0-9]{9}$/',                     // Netherlands
        '/^48[0-9]{9}$/',                     // Poland
        '/^351[0-9]{9}$/',                    // Portugal
        '/^400?[1-9]\d{8}$/',                 // Romania
        '/^386[0-9]{8}$/',                    // Slovenia
        '/^34[0-9]{9}$/',                     // Spain
        '/^46[0-9]{7,12}$/',                  // Sweden
        '/^41[0-9]{9}$/',                     // Switzerland
        '/^380[0-9]{7,9}$/',                  // Ukraine
        '/^44[0-9]{4,10}$/',                 // UK
    ];

    /**
     * Валидация номера телефона по паттернам стран
     * 
     * @param string $phone Номер телефона для проверки
     * @param bool $normalize Нормализовать номер перед проверкой
     * @return bool true если номер валиден, false в противном случае
     */
    public static function validate(string $phone, bool $normalize = true): bool
    {
        if (empty($phone) || !is_string($phone)) {
            return false;
        }

        // Нормализация номера перед проверкой
        if ($normalize) {
            $phone = self::normalize($phone);
        }

        // Проверка каждого паттерна
        foreach (self::$countryPatterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Простая проверка формата номера телефона (без проверки по странам)
     * 
     * @param string $phoneNumber Номер телефона
     * @return bool true если формат валиден
     */
    public static function isValidFormat(string $phoneNumber): bool
    {
        if (empty($phoneNumber)) {
            return false;
        }

        // Удаляем пробелы и дефисы
        $phone = preg_replace('/[\s\-]/', '', $phoneNumber);

        // Проверяем формат (должен начинаться с + или цифры, содержать только цифры и +)
        return preg_match('/^\+?[0-9]{9,15}$/', $phone) === 1;
    }

    /**
     * Нормализация номера телефона
     * Удаляет пробелы, дефисы, скобки и другие символы
     * Обрабатывает префикс + и заменяет начальный 0 на +421 для Словакии
     * 
     * @param string $phoneNumber Номер телефона
     * @param bool $removePlus Удалить префикс + из результата
     * @return string Нормализованный номер
     */
    public static function normalize(string $phoneNumber, bool $removePlus = true): string
    {
        if (empty($phoneNumber)) {
            return '';
        }

        // Удаляем пробелы, дефисы, скобки, точки и другие символы
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phoneNumber);

        // Если номер начинается с +, удаляем его для нормализации
        if (strpos($phone, '+') === 0) {
            $phone = substr($phone, 1);
        }

        // Если номер начинается с 0, заменяем на 421 (Slovakia)
        if (preg_match('/^0/', $phone)) {
            $phone = '421' . substr($phone, 1);
        }

        // Если нужно вернуть с префиксом +
        if (!$removePlus && !empty($phone)) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Нормализация номера с префиксом +
     * 
     * @param string $phoneNumber Номер телефона
     * @return string Нормализованный номер с префиксом +
     */
    public static function normalizeWithPlus(string $phoneNumber): string
    {
        return self::normalize($phoneNumber, false);
    }

    /**
     * Получение списка поддерживаемых паттернов стран
     * 
     * @return array Массив паттернов
     */
    public static function getCountryPatterns(): array
    {
        return self::$countryPatterns;
    }
}


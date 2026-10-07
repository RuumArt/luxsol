<?php

namespace Room\Services;

use Room\Tools\PhoneValidator;
use Exception;

/**
 * Класс для работы с SMS API (smstools.sk / O2 SMS Connector)
 * Документация: https://www.smstools.sk/downloads/SMSTOOLS-API-dokumentacia.pdf
 */
class SmsService
{
    /**
     * URL API для отправки SMS
     */
    const API_URL = 'https://api.smstools.sk/3/send_batch';

    /**
     * URL API с поддержкой TLS 1.2, 1.3
     */
    const API_URL_TLS = 'https://api-tls12.smstools.sk/3/send_batch';

    /**
     * Успешные HTTP статус коды
     */
    const HTTP_SUCCESS_CODES = [200, 201];

    /**
     * Максимальная длина SMS сообщения (GSM 3.38)
     */
    const MAX_SMS_LENGTH = 160;

    /**
     * Максимальная длина длинного SMS
     */
    const MAX_LONG_SMS_LENGTH = 1530;

    /**
     * @var string API ключ для авторизации
     */
    private $apiKey;

    /**
     * @var string Имя отправителя
     */
    private $senderName;

    /**
     * @var bool Использовать TLS 1.2/1.3 endpoint
     */
    private $useTls;

    /**
     * @var bool Включить логирование
     */
    private $enableLogging;

    /**
     * SmsService constructor.
     *
     * @param string $apiKey API ключ для авторизации
     * @param string $senderName Имя отправителя (по умолчанию 'Luxsol')
     * @param bool $useTls Использовать TLS endpoint
     * @param bool $enableLogging Включить логирование
     */
    public function __construct(
        string $apiKey,
        string $senderName = 'Luxsol',
        bool $useTls = false,
        bool $enableLogging = true
    ) {
        $this->apiKey = $apiKey;
        $this->senderName = $senderName;
        $this->useTls = $useTls;
        $this->enableLogging = $enableLogging;
    }

    /**
     * Отправка SMS сообщения одному получателю
     *
     * @param string $phoneNumber Номер телефона получателя
     * @param string $message Текст сообщения
     * @return array|false Результат отправки или false в случае ошибки
     */
    public function sendMessage(string $phoneNumber, string $message)
    {
        return $this->sendBatch([$phoneNumber], $message);
    }

    /**
     * Отправка SMS сообщения нескольким получателям
     *
     * @param array $phoneNumbers Массив номеров телефонов получателей
     * @param string $message Текст сообщения
     * @return array|false Результат отправки или false в случае ошибки
     */
    public function sendBatch(array $phoneNumbers, string $message)
    {
        try {
            // Валидация входных данных
            $this->validateInput($phoneNumbers, $message);

            // Подготовка данных для запроса
            $data = $this->prepareRequestData($phoneNumbers, $message);

            // Выполнение запроса
            $response = $this->makeRequest($data);

            // Логирование успешной отправки
            if ($this->enableLogging && $response !== false) {
                $this->log('SMS отправлено успешно', [
                    'recipients' => $phoneNumbers,
                    'message_length' => mb_strlen($message),
                    'response' => $response
                ]);
            }

            return $response;
        } catch (Exception $e) {
            // Логирование ошибки без прерывания выполнения
            $this->logError('Ошибка отправки SMS', [
                'phone_numbers' => $phoneNumbers,
                'message_preview' => mb_substr($message, 0, 50),
                'error' => $e->getMessage()
            ]);

            // Возвращаем false вместо исключения, чтобы не прерывать выполнение родительской функции
            return false;
        }
    }

    /**
     * Валидация входных данных
     *
     * @param array $phoneNumbers Массив номеров телефонов
     * @param string $message Текст сообщения
     * @throws Exception
     */
    private function validateInput(array $phoneNumbers, string $message): void
    {
        if (empty($phoneNumbers)) {
            throw new Exception('Не указаны номера получателей');
        }

        if (empty(trim($message))) {
            throw new Exception('Текст сообщения не может быть пустым');
        }

        if (mb_strlen($message) > self::MAX_LONG_SMS_LENGTH) {
            throw new Exception('Текст сообщения слишком длинный (максимум ' . self::MAX_LONG_SMS_LENGTH . ' символов)');
        }

        // Валидация номеров телефонов
        foreach ($phoneNumbers as $phone) {
            if (empty($phone) || !PhoneValidator::isValidFormat($phone)) {
                throw new Exception('Некорректный номер телефона: ' . $phone);
            }
        }
    }

    /**
     * Подготовка данных для запроса
     *
     * @param array $phoneNumbers Массив номеров телефонов
     * @param string $message Текст сообщения
     * @return array Данные для запроса
     */
    private function prepareRequestData(array $phoneNumbers, string $message): array
    {
        // Нормализация номеров телефонов
        $recipients = [];
        foreach ($phoneNumbers as $phone) {
            $recipients[] = [
                'phonenr' => PhoneValidator::normalizeWithPlus($phone)
            ];
        }

        return [
            'auth' => [
                'apikey' => $this->apiKey
            ],
            'data' => [
                'message' => $message,
                'sender' => [
                    'text' => $this->senderName
                ],
                'recipients' => $recipients
            ]
        ];
    }

    /**
     * Выполнение HTTP запроса к API
     *
     * @param array $data Данные для отправки
     * @return array|false Ответ API или false в случае ошибки
     */
    private function makeRequest(array $data)
    {
        $url = $this->useTls ? self::API_URL_TLS : self::API_URL;
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($jsonData === false) {
            $this->logError('Ошибка кодирования JSON', [
                'json_error' => json_last_error_msg()
            ]);
            return false;
        }

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json;charset=UTF-8'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);

        curl_close($curl);

        // Обработка ошибок CURL
        if ($response === false || $curlErrno !== 0) {
            $this->logError('Ошибка CURL при отправке SMS', [
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'http_code' => $httpCode,
                'url' => $url
            ]);
            return false;
        }

        // Проверка HTTP статус кода
        if (!in_array($httpCode, self::HTTP_SUCCESS_CODES)) {
            $this->logError('Ошибка HTTP при отправке SMS', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return false;
        }

        // Декодирование JSON ответа
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Ошибка декодирования JSON ответа', [
                'json_error' => json_last_error_msg(),
                'response' => $response
            ]);
            return false;
        }

        // Проверка на ошибки в ответе API
        if (isset($decodedResponse['id']) && $decodedResponse['id'] !== 'OK') {
            $this->logError('Ошибка API при отправке SMS', [
                'api_response' => $decodedResponse
            ]);
            return false;
        }

        return $decodedResponse;
    }

    /**
     * Логирование успешных операций
     *
     * @param string $message Сообщение
     * @param array $context Контекст
     */
    private function log(string $message, array $context = []): void
    {
        if (!$this->enableLogging) {
            return;
        }

        $logMessage = $message;
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $logMessage = self::maskPhones($logMessage);

        // Используем AddMessage2Log если доступна (Bitrix)
        if (function_exists('AddMessage2Log')) {
            AddMessage2Log($logMessage, 'sms_service');
        } else {
            error_log('[SmsService] ' . $logMessage);
        }
    }

    /**
     * Логирование ошибок
     *
     * @param string $message Сообщение об ошибке
     * @param array $context Контекст ошибки
     */
    private function logError(string $message, array $context = []): void
    {
        $logMessage = '[ERROR] ' . $message;
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $logMessage = self::maskPhones($logMessage);

        // Используем AddMessage2Log если доступна (Bitrix)
        if (function_exists('AddMessage2Log')) {
            AddMessage2Log($logMessage, 'sms_service');
        } else {
            error_log('[SmsService] ' . $logMessage);
        }
    }

    /**
     * Скрывает номера телефонов в тексте лога
     *
     * Для разбора проблем достаточно начала номера (страна, оператор)
     * и двух последних цифр: +421908190492 -> +42190*****92.
     * Разделителем считается только пробел: с дефисом под шаблон
     * попадали бы даты вида 2026-09-17
     *
     * @param string $text
     * @return string
     */
    private static function maskPhones(string $text): string
    {
        return (string)preg_replace_callback(
            '/\+?\d[\d ]{7,}\d/',
            static function (array $match): string {
                $value = $match[0];
                $digits = preg_replace('/\D/', '', $value);

                // Короткие числа - суммы, номера заказов - не трогаем
                if (strlen($digits) < 9) {
                    return $value;
                }

                $prefix = (strpos($value, '+') === 0 ? '+' : '') . substr($digits, 0, 5);

                return $prefix . str_repeat('*', strlen($digits) - 7) . substr($digits, -2);
            },
            $text
        );
    }

    /**
     * Получение информации о количестве SMS частей для сообщения
     *
     * @param string $message Текст сообщения
     * @return int Количество SMS частей
     */
    public function getSmsCount(string $message): int
    {
        $length = mb_strlen($message);
        
        if ($length <= self::MAX_SMS_LENGTH) {
            return 1;
        }

        // Для длинных SMS (GSM 3.38) каждая часть до 153 символов
        return (int)ceil($length / 153);
    }

    /**
     * Проверка доступности API
     *
     * @return bool true если API доступно
     */
    public function checkApiAvailability(): bool
    {
        try {
            // Простая проверка доступности через HEAD запрос
            $url = $this->useTls ? self::API_URL_TLS : self::API_URL;
            $curl = curl_init($url);

            curl_setopt_array($curl, [
                CURLOPT_NOBODY => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);

            curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            return $httpCode > 0 && empty($curlError);
        } catch (Exception $e) {
            return false;
        }
    }
}


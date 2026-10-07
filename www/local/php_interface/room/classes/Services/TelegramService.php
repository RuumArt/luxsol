<?php

namespace Room\Services;

use Exception;

/**
 * Класс для работы с Telegram Bot API
 * Документация: https://core.telegram.org/bots/api
 */
class TelegramService
{
    /**
     * Базовый URL для Telegram Bot API
     */
    const API_BASE_URL = 'https://api.telegram.org/bot';

    /**
     * Успешные HTTP статус коды
     */
    const HTTP_SUCCESS_CODES = [200];

    /**
     * Максимальная длина текстового сообщения
     */
    const MAX_MESSAGE_LENGTH = 4096;

    /**
     * @var string Токен бота
     */
    private $token;

    /**
     * @var string ID чата по умолчанию
     */
    private $defaultChatId;

    /**
     * @var bool Включить логирование
     */
    private $enableLogging;

    /**
     * TelegramService constructor.
     *
     * @param string $token Токен бота
     * @param string $defaultChatId ID чата по умолчанию (username или chat_id)
     * @param bool $enableLogging Включить логирование
     */
    public function __construct(
        string $token,
        string $defaultChatId = '',
        bool $enableLogging = true
    ) {
        $this->token = $token;
        $this->defaultChatId = $defaultChatId;
        $this->enableLogging = $enableLogging;
    }

    /**
     * Отправка сообщения в Telegram
     *
     * @param string $message Текст сообщения
     * @param string|null $chatId ID чата (если не указан, используется defaultChatId)
     * @param array $options Дополнительные опции (parse_mode, disable_web_page_preview и т.д.)
     * @return array|false Результат отправки или false в случае ошибки
     */
    public function sendMessage(string $message, ?string $chatId = null, array $options = [])
    {
        try {
            // Валидация входных данных
            $this->validateInput($message);

            // Определение chat_id
            $targetChatId = $chatId ?? $this->defaultChatId;
            if (empty($targetChatId)) {
                throw new Exception('Не указан ID чата для отправки сообщения');
            }

            // Подготовка данных для запроса
            $url = $this->buildUrl('sendMessage', $targetChatId, $message, $options);

            // Выполнение запроса
            $response = $this->makeRequest($url);

            // Логирование успешной отправки
            if ($this->enableLogging && $response !== false) {
                $this->log('Telegram сообщение отправлено успешно', [
                    'chat_id' => $targetChatId,
                    'message_length' => mb_strlen($message),
                    'response' => $response
                ]);
            }

            return $response;
        } catch (Exception $e) {
            // Логирование ошибки без прерывания выполнения
            $this->logError('Ошибка отправки Telegram сообщения', [
                'chat_id' => $chatId ?? $this->defaultChatId,
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
     * @param string $message Текст сообщения
     * @throws Exception
     */
    private function validateInput(string $message): void
    {
        if (empty(trim($message))) {
            throw new Exception('Текст сообщения не может быть пустым');
        }

        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new Exception('Текст сообщения слишком длинный (максимум ' . self::MAX_MESSAGE_LENGTH . ' символов)');
        }
    }

    /**
     * Построение URL для запроса
     *
     * @param string $method Метод API
     * @param string $chatId ID чата
     * @param string $message Текст сообщения
     * @param array $options Дополнительные опции
     * @return string URL для запроса
     */
    private function buildUrl(string $method, string $chatId, string $message, array $options = []): string
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $message
        ];

        // Добавление дополнительных опций
        if (isset($options['parse_mode'])) {
            $params['parse_mode'] = $options['parse_mode'];
        }

        if (isset($options['disable_web_page_preview'])) {
            $params['disable_web_page_preview'] = $options['disable_web_page_preview'] ? 'true' : 'false';
        }

        if (isset($options['disable_notification'])) {
            $params['disable_notification'] = $options['disable_notification'] ? 'true' : 'false';
        }

        $queryString = http_build_query($params);
        return self::API_BASE_URL . $this->token . '/' . $method . '?' . $queryString;
    }

    /**
     * Выполнение HTTP запроса к API
     *
     * @param string $url URL для запроса
     * @return array|false Ответ API или false в случае ошибки
     */
    private function makeRequest(string $url)
    {
        $ch = curl_init();
        $optArray = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ];
        curl_setopt_array($ch, $optArray);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);

        curl_close($ch);

        // Обработка ошибок CURL
        if ($result === false || $curlErrno !== 0) {
            $this->logError('Ошибка CURL при отправке Telegram сообщения', [
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'http_code' => $httpCode,
                'url' => $url
            ]);
            return false;
        }

        // Проверка HTTP статус кода
        if (!in_array($httpCode, self::HTTP_SUCCESS_CODES)) {
            $this->logError('Ошибка HTTP при отправке Telegram сообщения', [
                'http_code' => $httpCode,
                'response' => $result
            ]);
            return false;
        }

        // Декодирование JSON ответа
        $decodedResponse = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Ошибка декодирования JSON ответа', [
                'json_error' => json_last_error_msg(),
                'response' => $result
            ]);
            return false;
        }

        // Проверка на ошибки в ответе API
        if (isset($decodedResponse['ok']) && $decodedResponse['ok'] !== true) {
            $errorDescription = $decodedResponse['description'] ?? 'Unknown error';
            $this->logError('Ошибка API при отправке Telegram сообщения', [
                'api_response' => $decodedResponse,
                'error_description' => $errorDescription
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

        // Используем AddMessage2Log если доступна (Bitrix)
        if (function_exists('AddMessage2Log')) {
            AddMessage2Log($logMessage, 'telegram_service');
        } else {
            error_log('[TelegramService] ' . $logMessage);
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

        // Используем AddMessage2Log если доступна (Bitrix)
        if (function_exists('AddMessage2Log')) {
            AddMessage2Log($logMessage, 'telegram_service');
        } else {
            error_log('[TelegramService] ' . $logMessage);
        }
    }

    /**
     * Проверка доступности API
     *
     * @return bool true если API доступно
     */
    public function checkApiAvailability(): bool
    {
        try {
            // Простая проверка доступности через getMe метод
            $url = self::API_BASE_URL . $this->token . '/getMe';
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && !empty($result)) {
                $response = json_decode($result, true);
                return isset($response['ok']) && $response['ok'] === true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}

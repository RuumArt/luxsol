<?php
namespace Sale\Handlers\PaySystem;

require $_SERVER['DOCUMENT_ROOT'] . "/local/vendor/autoload.php";

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;

use Room\Services\Webpay\Api;
use Room\Services\Webpay\Signer;
use Room\Services\Webpay\PaymentRequest;
use Room\Services\Webpay\ResponseVerifier;
use Room\Services\Webpay\PaymentRequest\AddInfo;

Loc::loadMessages(__FILE__);

/**
 * Включение режима отладки для диагностики PRCODE=31 (Wrong signature)
 * 
 * Для включения отладки раскомментируйте следующую строку:
 * define('GPWEBPAY_DEBUG', true);
 * 
 * Логи будут записываться в error_log PHP и Bitrix ErrorLog
 * После устранения проблемы обязательно отключите отладку!
 */
if (!defined('GPWEBPAY_DEBUG')) {
    define('GPWEBPAY_DEBUG', true);
}

/**
 * Обработчик платежной системы GP WebPay для Bitrix D7
 * 
 * @see https://github.com/newPOPE/gp-webpay-php-sdk
 * @see https://g-rain-design.ru/blog/posts/payment-handlers-bitrix-d7/
 */
class GpWebHandler extends PaySystem\ServiceHandler
{
    /**
     * Коды валют ISO 4217
     */
    const CURRENCY_EUR = 978;
    
    /**
     * Коды операций GP WebPay
     */
    const OPERATION_CREATE_ORDER = 'CREATE_ORDER';
    const OPERATION_STATUS = 'STATUS';
    
    /**
     * Коды результата PRCODE
     */
    const PRCODE_SUCCESS = 0;
    
    /**
     * Инициализация платежа
     * 
     * @param Payment $payment Объект платежа
     * @param Request|null $request Объект запроса
     * @return PaySystem\ServiceResult
     */
    public function initiatePay(Payment $payment, Request $request = null)
    {
        $result = new PaySystem\ServiceResult();
        
        try {
            // Получаем конфигурацию
            $config = $this->getConfig($payment);

            if (!$config) {
                $result->addError(new Error(Loc::getMessage('SALE_HPS_GPWEB_ERROR_CONFIG')));
                return $result;
            }

            // Инициализируем платежный запрос
            $paymentRequest = $this->createPaymentRequest($payment);

            // Создаем подписант
            $signer = $this->createSigner($config);

            // Создаем API клиент
            $api = new Api(
                $config['merchant_number'],
                $config['gateway_url'],
                $signer
            );
            
            // Получаем URL для редиректа
            $paymentUrl = $api->createPaymentRequestUrl($paymentRequest);
            
            // Сохраняем номер заказа в платеже
            $params = $paymentRequest->getParams();
            
            $payment->setField('PS_STATUS_MESSAGE', $params['ORDERNUMBER']);
            $payment->setField('PS_INVOICE_ID', (string)$params['ORDERNUMBER']);
            $payment->save();
            
            // Устанавливаем параметры для шаблона
            $this->setExtraParams([
                'URL' => $paymentUrl,
                'ORDERID' => $params['MERORDERNUM'],
                'PS_MODE' => $this->service->getField('PS_MODE'),
                'BX_PAYSYSTEM_CODE' => $this->service->getField('ID'),
            ]);
            
            return $this->showTemplate($payment, "template");
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();
            
            // Детальное логирование ошибки
            $this->logError('initiatePay', $errorMessage);
            $this->logError('initiatePay', 'Exception trace: ' . $errorTrace);
            
            // Если это ошибка подписи, добавляем дополнительную информацию
            if (strpos($errorMessage, 'sign') !== false || strpos($errorMessage, 'digest') !== false) {
                $this->logError('initiatePay', 'Signature error detected. Check digest text and parameters above.');
            }
            
            $result->addError(new Error($errorMessage));
            return $result;
        }
    }
    
    /**
     * Получить список поддерживаемых валют
     * 
     * @return array
     */
    public function getCurrencyList()
    {
        return ['EUR'];
    }
    
    /**
     * Получить поля для идентификации платежа
     * 
     * @return array
     */
    public static function getIndicativeFields()
    {
        return ['MERORDERNUM'];
    }
    
    /**
     * Проверка расширенного ответа
     * 
     * @param Request $request
     * @param int $paySystemId
     * @return bool
     */
    protected static function isMyResponseExtended(Request $request, $paySystemId)
    {
        return true;
    }
    
    /**
     * Получить ID платежа из запроса
     * 
     * @param Request $request
     * @return int
     */
    public function getPaymentIdFromRequest(Request $request)
    {
        $paymentId = $request->getRaw('MERORDERNUM');
        if (is_string($paymentId) && preg_match('/^[0-9]{1,30}$/D', $paymentId)) {
            // Удаляем ведущие нули
            $paymentId = preg_replace("/^[0]+/", "", $paymentId);
            return (int)$paymentId;
        }
        
        return 0;
    }
    
    /**
     * Обработка ответа от платежной системы
     * 
     * @param Payment $payment
     * @param Request $request
     * @return PaySystem\ServiceResult
     */
    public function processRequest(Payment $payment, Request $request)
    {
        $result = new PaySystem\ServiceResult();
        
        try {
            // Проверяем исходные байты до чтения статуса и изменения платежа.
            $params = [];
            foreach (array_merge(ResponseVerifier::SIGNED_FIELDS, ['DIGEST', 'DIGEST1']) as $field) {
                $value = $request->getRaw($field);
                if ($value !== null) {
                    $params[$field] = $value;
                }
            }

            ResponseVerifier::verify(
                $params,
                (string)$this->getBusinessValue($payment, 'GPWEB_MERCHANT_NUMBER'),
                $this->resolveKeyPath((string)$this->getBusinessValue($payment, 'GPWEB_PUBLIC_KEY_PATH'))
            );

            // Подпись должна относиться к выбранному платежу и сохранённой попытке.
            $expectedOrderNumber = (string)$payment->getField('PS_INVOICE_ID');
            if ($expectedOrderNumber === '') {
                // Совместимость с попытками, созданными до сохранения PS_INVOICE_ID.
                $expectedOrderNumber = (string)$payment->getField('PS_STATUS_MESSAGE');
            }
            if (ltrim($params['MERORDERNUM'], '0') !== (string)$payment->getId() ||
                $expectedOrderNumber === '' || $params['ORDERNUMBER'] !== $expectedOrderNumber) {
                throw new \RuntimeException('GP webpay response does not match the payment attempt.');
            }

            $prCode = (int)$params['PRCODE'];
            $srCode = (int)$params['SRCODE'];
            $resultText = $params['RESULTTEXT'] ?? '';
            $orderNumber = $params['ORDERNUMBER'];
            
            // Извлекаем дополнительные данные
            $data = $this->extractDataFromResponse($params);
            
            // Подготавливаем поля для сохранения
            $fields = [
                'PS_STATUS_CODE' => $prCode,
                'PS_STATUS_MESSAGE' => $resultText,
                'PS_SUM' => $payment->getSum(),
                'PS_CURRENCY' => $payment->getField('CURRENCY'),
                'PS_RESPONSE_DATE' => new DateTime(),
                'PS_INVOICE_ID' => $orderNumber ?: '',
            ];
            
            // Обрабатываем результат платежа
            if ($prCode === self::PRCODE_SUCCESS && $srCode === 0) {
                // Проверяем, нужно ли менять статус и не оплачен ли уже платеж
                $shouldChangeStatus = $this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y';
                
                if (!$payment->isPaid() && $shouldChangeStatus) {
                    $fields['PS_STATUS'] = 'Y';
                    $fields['PS_STATUS_DESCRIPTION'] = Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_SUCCESS');
                    $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
                    $data['CODE'] = 0;
                } else {
                    $fields['PS_STATUS'] = 'N';
                    if ($payment->isPaid()) {
                        $fields['PS_STATUS_DESCRIPTION'] = Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_ALREADY_PAID');
                        $result->addError(new Error(Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_ALREADY_PAID')));
                    } else {
                        $fields['PS_STATUS_DESCRIPTION'] = Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_STATUS_DISABLED');
                        $result->addError(new Error(Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_STATUS_DISABLED')));
                    }
                    $data['CODE'] = 200;
                }
            } else {
                // Ошибка платежа
                $errorMessage = $resultText ?: Loc::getMessage('SALE_HPS_GPWEB_PAYMENT_ERROR');
                
                // Детальное логирование для PRCODE=31 (Wrong signature)
                if ($prCode == 31) {
                    $this->logError('processRequest', 'PRCODE=31 (Wrong signature) detected!');
                    $this->logError('processRequest', 'SRCODE: ' . $srCode);
                    $this->logError('processRequest', 'RESULTTEXT: ' . $resultText);
                    $this->logError('processRequest', 'ORDERNUMBER: ' . $orderNumber);
                    $this->logError('processRequest', 'MERORDERNUM: ' . $params['MERORDERNUM']);
                    
                    $errorMessage = 'Ошибка подписи (PRCODE=31). Проверьте логи для деталей. ' . $resultText;
                }
                
                $fields['PS_STATUS'] = 'N';
                $fields['PS_STATUS_DESCRIPTION'] = $errorMessage;
                $result->addError(new Error($errorMessage));
                $data['CODE'] = $prCode;
            }
            
            $result->setPsData($fields);
            $result->setData($data);
            
        } catch (\Throwable $e) {
            $result->addError(new Error(Loc::getMessage('SALE_HPS_GPWEB_ERROR_RESPONSE')));
            $this->logError('processRequest', $e->getMessage());
        }
        
        // Логируем ошибки, если есть
        if (!$result->isSuccess()) {
            $this->logError('processRequest', implode('; ', $result->getErrorMessages()));
        }
        
        return $result;
    }
    
    /**
     * Извлечь данные из запроса
     * 
     * @param array $params Проверенные поля ответа
     * @return array
     */
    private function extractDataFromResponse(array $params)
    {
        $operation = $params['OPERATION'];
        
        return [
            'HEAD' => $operation ? $operation . 'Response' : '',
            'MERORDERNUM' => $params['MERORDERNUM'],
            'ORDERNUMBER' => $params['ORDERNUMBER'],
            'OPERATION' => $operation,
            'PRCODE' => $params['PRCODE'],
            'SRCODE' => $params['SRCODE'],
            'RESULTTEXT' => $params['RESULTTEXT'] ?? '',
        ];
    }
    
    /**
     * Создать платежный запрос
     * 
     * @param Payment $payment
     * @return PaymentRequest
     * @throws \Exception
     */
    private function createPaymentRequest(Payment $payment)
    {
        $orderId = $payment->getOrderId();
        $order = Order::load($orderId);
        
        if (!$order) {
            throw new \Exception(Loc::getMessage('SALE_HPS_GPWEB_ERROR_ORDER_NOT_FOUND'));
        }
        
        // Формируем уникальный номер заказа для GP WebPay
        // ORDERNUMBER должен быть уникальным для каждого запроса от мерчанта
        // Используем комбинацию ID заказа и timestamp для гарантии уникальности
        $uniqueOrderId = (int)($orderId . time());
        
        // Получаем данные клиента
        $propertyCollection = $order->getPropertyCollection();
        $email = $propertyCollection->getUserEmail()->getValue();
        $name = $propertyCollection->getPayerName()->getValue();
        
        // Форматируем имя (транслитерация для ASCII)
        $formattedName = iconv('UTF-8', 'ASCII//TRANSLIT', $name ?: '');

        // URL для callback
        $callbackUrl = $this->getCallbackUrl();

        // Получаем код валюты из настроек
        $currencyCode = (int)$this->getBusinessValue($payment, 'GPWEB_CURRENCY') ?: self::CURRENCY_EUR;

        // Загружаем схему для дополнительной информации
        $schemaPath = __DIR__ . '/GPwebpayAdditionalInfoRequest_v.5.xsd';

        if (!file_exists($schemaPath)) {
            throw new \Exception(Loc::getMessage('SALE_HPS_GPWEB_ERROR_SCHEMA_NOT_FOUND'));
        }

        $schema = file_get_contents($schemaPath);

        // Создаем дополнительную информацию
        $addInfo = new AddInfo(
            $schema,
            array_merge(
                AddInfo::createMinimalValues(),
                [
                    'cardholderInfo' => [
                        'cardholderDetails' => [
                            'name' => $formattedName,
                            'email' => $email ?: '',
                        ],
                    ],
                ]
            )
        );

        // Создаем платежный запрос
        // Согласно GP WebPay WS API v1.18, используем PaymentRequest с поддержкой всех новых полей
        $paymentRequest = new PaymentRequest(
            $uniqueOrderId, // ORDERNUMBER - уникальный номер платежа для GP WebPay
            $payment->getSum(),
            $currencyCode,
            1, // DEPOSITFLAG - 1 = immediate payment required
            $callbackUrl,
            (string)$payment->getId(), // MERORDERNUM возвращается как ID платежа Битрикса
            null, // MD - Merchant data (optional)
            $addInfo, // ADDINFO - Additional info with cardholder data (PSD2 requirement)
            PaymentRequest::PAYMENT_CARD // PAYMETHOD - Default payment method
        );
        
        return $paymentRequest;
    }
    
    /**
     * Создать подписант
     * 
     * @param array $config
     * @return Signer
     * @throws \Exception
     */
    private function createSigner(array $config)
    {
        $privateKeyPath = $this->resolveKeyPath($config['private_key_path']);
        $publicKeyPath = $this->resolveKeyPath($config['public_key_path']);
        
        if (!file_exists($privateKeyPath)) {
            throw new \Exception(Loc::getMessage('SALE_HPS_GPWEB_ERROR_PRIVATE_KEY_NOT_FOUND'));
        }
        
        if (!file_exists($publicKeyPath)) {
            throw new \Exception(Loc::getMessage('SALE_HPS_GPWEB_ERROR_PUBLIC_KEY_NOT_FOUND'));
        }
        
        return new Signer(
            $privateKeyPath,
            $config['private_key_password'],
            $publicKeyPath
        );
    }
    
    /**
     * Получить конфигурацию из настроек обработчика
     * 
     * @param Payment $payment Объект платежа
     * @return array|null
     */
    private function getConfig(Payment $payment)
    {
        // Получаем параметры через getBusinessValue
        $merchantNumber = $this->getBusinessValue($payment, 'GPWEB_MERCHANT_NUMBER');
        $privateKeyPath = $this->getBusinessValue($payment, 'GPWEB_PRIVATE_KEY_PATH');
        $privateKeyPassword = $this->getBusinessValue($payment, 'GPWEB_PRIVATE_KEY_PASSWORD');
        $publicKeyPath = $this->getBusinessValue($payment, 'GPWEB_PUBLIC_KEY_PATH');
        $gatewayUrl = $this->getBusinessValue($payment, 'GPWEB_GATEWAY_URL');
        
        if (empty($merchantNumber) || empty($privateKeyPath) || empty($publicKeyPath)) {
            return null;
        }
        
        return [
            'merchant_number' => $merchantNumber,
            'private_key_path' => $privateKeyPath,
            'private_key_password' => $privateKeyPassword ?: '',
            'public_key_path' => $publicKeyPath,
            'gateway_url' => $gatewayUrl ?: 'https://3dsecure.gpwebpay.com/pgw/order.do',
        ];
    }
    
    /**
     * Разрешить путь к ключу (относительный или абсолютный)
     * 
     * @param string $path
     * @return string
     */
    private function resolveKeyPath($path)
    {
        if (empty($path)) {
            return '';
        }
        
        // Если абсолютный путь
        if (strpos($path, '/') === 0 || preg_match('/^[A-Z]:\\\\/i', $path)) {
            return $path;
        }
        
        // Относительный путь от папки обработчика
        return __DIR__ . '/' . ltrim($path, '/');
    }
    
    /**
     * Получить URL для callback
     * 
     * @return string
     */
    private function getCallbackUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        return $protocol . '://' . $host . '/bitrix/tools/sale_ps_result.php';
    }
    
    /**
     * Логировать ошибку
     * 
     * @param string $action
     * @param string $message
     */
    private function logError($action, $message)
    {
        PaySystem\ErrorLog::add([
            'ACTION' => $action,
            'MESSAGE' => $message,
            'DATE_INSERT' => new DateTime(),
        ]);
    }
}

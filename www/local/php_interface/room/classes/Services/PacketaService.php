<?php

namespace Room\Services;

use Bitrix\Sale\Order;
use Room\Tools\PhoneValidator;
use SoapClient;
use SoapFault;
use Exception;

/**
 * Класс для работы с Packeta (Zasilkovna) API
 */
class PacketaService
{
    /**
     * URL WSDL для Packeta API
     */
    const WSDL_URL = 'http://www.zasilkovna.cz/api/soap.wsdl';

    /**
     * ID платежной системы для COD (Cash on Delivery)
     */
    const PAYMENT_SYSTEM_COD_ID = 6;

    /**
     * Минимальный вес в кг (если вес не указан)
     */
    const DEFAULT_WEIGHT = 1.0;

    /**
     * Валюта по умолчанию
     */
    const DEFAULT_CURRENCY = 'EUR';

    /**
     * @var string API пароль для Packeta
     */
    private $apiPassword;

    /**
     * @var SoapClient|null SOAP клиент
     */
    private $soapClient;

    /**
     * PacketaService constructor.
     *
     * @param string $apiPassword API пароль для Packeta
     */
    public function __construct(string $apiPassword)
    {
        $this->apiPassword = $apiPassword;
    }

    /**
     * Создание посылки в Packeta на основе заказа Bitrix
     *
     * @param Order $order Заказ Bitrix
     * @return array|false Результат создания посылки или false в случае ошибки
     * @throws Exception
     */
    public function createPacket(Order $order)
    {
        try {
            $attributes = $this->preparePacketAttributes($order);

            if (!$this->validateAttributes($attributes)) {
                throw new Exception('Не все обязательные поля заполнены для создания посылки Packeta');
            }

            $client = $this->getSoapClient();
            $result = $client->createPacket($this->apiPassword, $attributes);

            return $result;
        } catch (SoapFault $e) {
            // Логирование ошибки SOAP
            error_log('Packeta SOAP Error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log('Packeta Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Подготовка атрибутов для создания посылки
     *
     * @param Order $order Заказ Bitrix
     * @return array Атрибуты посылки
     */
    private function preparePacketAttributes(Order $order): array
    {
        $propertyCollection = $order->getPropertyCollection();
        $basket = $order->getBasket();

        $orderId = $order->getId();
        $orderPrice = $order->getPrice();
        $orderPays = $order->getPaymentSystemId();

        // Определение COD
        $orderCod = $this->calculateCod($orderPays, $orderPrice);

        // Получение данных получателя (основные)
        $mainName = $this->getPropertyByCode($propertyCollection, 'FIO');
        $mainLastName = $this->getPropertyByCode($propertyCollection, 'LAST_NAME');
        $mainEmail = $this->getPropertyByCode($propertyCollection, 'EMAIL');
        $mainPhone = $this->getPropertyByCode($propertyCollection, 'PHONE');

        // Получение данных получателя (дополнительные - для доставки)
        $name = $this->getPropertyByCode($propertyCollection, 'FIO_2');
        $lastName = $this->getPropertyByCode($propertyCollection, 'LAST_NAME_2');
        $phone = $this->getPropertyByCode($propertyCollection, 'PHONE_2');

        // ID пункта выдачи Packeta
        $packetaId = $this->getPropertyByCode($propertyCollection, 'zas_id');

        // Вес посылки
        $weight = $basket->getWeight();
        $normalWeight = !empty($weight) ? round($weight / 1000, 2) : self::DEFAULT_WEIGHT;

        // Выбор телефона (приоритет у дополнительного)
        $correctPhone = !empty($phone) ? $phone : $mainPhone;

        // Формирование атрибутов
        $attributes = [
            'number' => '0' . $orderId,
            'name' => !empty($name) ? $name : $mainName,
            'surname' => !empty($lastName) ? $lastName : $mainLastName,
            'email' => $mainEmail,
            'addressId' => (int)$packetaId,
            'value' => $orderPrice,
            'weight' => $normalWeight,
            'currency' => self::DEFAULT_CURRENCY,
            'cod' => $orderCod,
        ];

        // Добавление телефона, если он валиден
        if (PhoneValidator::validate($correctPhone)) {
            $attributes['phone'] = $correctPhone;
        }

        return $attributes;
    }

    /**
     * Расчет суммы COD
     *
     * @param array|int $paymentSystemIds ID платежных систем
     * @param float $orderPrice Цена заказа
     * @return float Сумма COD (0 если не COD)
     */
    private function calculateCod($paymentSystemIds, float $orderPrice): float
    {
        if (is_array($paymentSystemIds) && in_array(self::PAYMENT_SYSTEM_COD_ID, $paymentSystemIds)) {
            return $orderPrice;
        }

        return 0;
    }

    /**
     * Получение значения свойства заказа по коду
     *
     * @param \Bitrix\Sale\PropertyValueCollection $propertyCollection Коллекция свойств
     * @param string $code Код свойства
     * @return string Значение свойства
     */
    private function getPropertyByCode($propertyCollection, string $code): string
    {
        foreach ($propertyCollection as $property) {
            if ($property->getField('CODE') == $code) {
                $value = $property->getValue();
                return !empty($value) ? $value : '';
            }
        }

        return '';
    }


    /**
     * Валидация атрибутов перед отправкой
     *
     * @param array $attributes Атрибуты посылки
     * @return bool true если все обязательные поля заполнены
     */
    private function validateAttributes(array $attributes): bool
    {
        $requiredFields = ['number', 'name', 'surname', 'email', 'addressId', 'value', 'weight', 'currency'];

        foreach ($requiredFields as $field) {
            if (!isset($attributes[$field]) || empty($attributes[$field])) {
                return false;
            }
        }

        // Проверка, что addressId - положительное число
        if (!is_numeric($attributes['addressId']) || $attributes['addressId'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Получение SOAP клиента (ленивая инициализация)
     *
     * @return SoapClient SOAP клиент
     * @throws Exception
     */
    private function getSoapClient(): SoapClient
    {
        if ($this->soapClient === null) {
            try {
                $this->soapClient = new SoapClient(self::WSDL_URL);
            } catch (SoapFault $e) {
                throw new Exception('Ошибка подключения к Packeta API: ' . $e->getMessage());
            }
        }

        return $this->soapClient;
    }

    /**
     * Установка SOAP клиента (для тестирования)
     *
     * @param SoapClient $client SOAP клиент
     */
    public function setSoapClient(SoapClient $client): void
    {
        $this->soapClient = $client;
    }
}


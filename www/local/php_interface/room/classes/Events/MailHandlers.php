<?php

namespace Room\Events;

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Catalog\ProductTable;
use Room\Helpers\PropertyHelper;
use Room\Tools\Format;

use Room\Services\TelegramService;

Loader::includeModule('sale');

class MailHandlers
{
    /**
     * Обработчик события перед добавлением почтового события
     * 
     * @param string $event Тип события
     * @param string $lid ID сайта
     * @param array $arFields Поля события
     * @return void
     */
    public static function OnBeforeEventAddHandler($orderID, &$eventName, &$arFields)
    {
        $orderId = (int)($orderID ?? 0);

        if (!$orderId) {
            return;
        }

        $order = Order::load($orderId);

        if (!$order) {
            return;
        }

        $arFields["ORDER_ID"] = $orderId;

        // Формируем список товаров заказа
        $arFields["ORDER_TABLE_ITEMS"] = self::formatOrderItems($order);

        // Формируем информацию о заказе
        $arFields["ORDER_INFO"] = self::formatOrderInfo($order, $arFields);

        // Формируем краткую информацию о заказе (SHORT_ORDER)
        $arFields["SHORT_ORDER"] = self::formatShortOrder($order);

        $telegramService = new TelegramService(
            defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '',
            defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : ''
        );

        $telegramService->sendMessage($arFields["SHORT_ORDER"]);
    }

    /**
     * Форматирует список товаров заказа для email
     * 
     * @param Order $order Заказ
     * @return string HTML-строка со списком товаров
     */
    private static function formatOrderItems(Order $order): string
    {
        $itemsHtml = '';
        $basket = $order->getBasket();

        foreach ($basket as $basketItem) {
            $arMeasure = ProductTable::getCurrentRatioWithMeasure($basketItem->getProductId());
            $basketPropertyCollection = $basketItem->getPropertyCollection();
            $props = $basketPropertyCollection->getPropertyValues();

            $itemName = htmlspecialcharsbx($basketItem->getField('NAME'));
            $itemsHtml .= "<p>{$itemName}";

            // Каждый размер отдельной строкой: "2 × 3 m – 4 ks"
            $sizeLines = !empty($props['SIZES_STR']['VALUE'])
                ? Format::sizeLines($props['SIZES_STR']['VALUE'])
                : [];

            foreach ($sizeLines as $sizeLine) {
                $itemsHtml .= "<br>" . htmlspecialcharsbx($sizeLine);
            }

            if (!empty($props['SQUARE']['VALUE'])) {
                $itemsHtml .= "<br>Plocha: " . htmlspecialcharsbx(Format::number($props['SQUARE']['VALUE'])) . " m<sup>2</sup>";
            }
            if (!empty($props['COLOR']['VALUE'])) {
                $itemsHtml .= "<br>Farba: " . htmlspecialcharsbx($props['COLOR']['VALUE']);
            }

            // Форматируем цену и количество
            $price = Format::price($basketItem->getPrice());
            if (!empty($sizeLines)) {
                $itemsHtml .= "<br><strong>{$price}</strong></p>";
            } else {
                $quantity = Format::number($basketItem->getQuantity());
                $measureSymbol = $arMeasure[$basketItem->getProductId()]['MEASURE']['SYMBOL_RUS'] ?? '';
                $itemsHtml .= "<br>{$quantity} {$measureSymbol} × <strong>{$price}</strong></p>";
            }
        }

        return $itemsHtml;
    }

    /**
     * Форматирует информацию о заказе для email
     * 
     * @param Order $order Заказ
     * @param array $arFields Поля события (для сохранения LAST_NAME)
     * @return string HTML-строка с информацией о заказе
     */
    private static function formatOrderInfo(Order $order, array &$arFields): string
    {
        $propsCollection = $order->getPropertyCollection();
        $html = '';

        // Информация о покупателе
        $html .= self::formatCustomerInfo($order, $propsCollection, $arFields);

        // Информация о компании
        $html .= self::formatCompanyInfo($propsCollection);

        // Адрес доставки
        $html .= self::formatDeliveryAddress($propsCollection);

        // Информация о доставке и оплате
        $html .= self::formatShippingAndPayment($order, $propsCollection);

        return $html;
    }

    /**
     * Форматирует информацию о покупателе
     * 
     * @param Order $order Заказ
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @param array $arFields Поля события (для сохранения LAST_NAME)
     * @return string HTML-строка
     */
    private static function formatCustomerInfo(Order $order, $propsCollection, array &$arFields): string
    {
        $html = '';
        $fieldMap = [
            'FIO' => 'Meno',
            'LAST_NAME' => 'Priezvisko',
            'Adresa' => 'Adresa',
            'Mesto' => 'Mesto',
            'PSС' => 'PSČ',
            'EMAIL' => 'E-mail',
            'PHONE' => 'Telefón',
        ];

        foreach ($fieldMap as $code => $label) {
            $value = self::getPropertyValue($propsCollection, $code);
            if (!empty($value)) {
                $html .= "<p><strong>{$label}:</strong> " . htmlspecialcharsbx($value) . "</p>";
                
                // Сохраняем LAST_NAME в поля события
                if ($code === 'LAST_NAME') {
                    $arFields["LAST_NAME"] = $value;
                }
            }
        }

        // Описание заказа (примечание)
        $userDescription = $order->getField('USER_DESCRIPTION');
        if (!empty($userDescription)) {
            $html .= '<p><strong>Poznámka:</strong> ' . htmlspecialcharsbx($userDescription) . '</p>';
        }

        return $html;
    }

    /**
     * Форматирует информацию о компании
     * 
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @return string HTML-строка
     */
    private static function formatCompanyInfo($propsCollection): string
    {
        $html = '';
        $fieldMap = [
            'Firma' => 'Spoločnosť',
            'IČO' => 'IČO',
            'IČ DPH' => 'IČ DPH',
            'DIČ' => 'DIČ',
        ];

        foreach ($fieldMap as $code => $label) {
            $value = self::getPropertyValue($propsCollection, $code);
            if (!empty($value)) {
                $html .= "<p><strong>{$label}:</strong> " . htmlspecialcharsbx($value) . "</p>";
            }
        }

        return $html;
    }

    /**
     * Форматирует адрес доставки
     * 
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @return string HTML-строка
     */
    private static function formatDeliveryAddress($propsCollection): string
    {
        $html = '<p><strong>Doručenie na inú adresu</strong></p>';
        
        $fieldMap = [
            'FIO_2' => 'Meno',
            'LAST_NAME_2' => 'Priezvisko',
            'Adresa_2' => 'Adresa',
            'Mesto_2' => 'Mesto',
            'PSС_2' => 'PSČ',
            'PHONE_2' => 'Telefón',
        ];

        foreach ($fieldMap as $code => $label) {
            $value = self::getPropertyValue($propsCollection, $code);
            if (!empty($value)) {
                $html .= "<p><strong>{$label}:</strong> " . htmlspecialcharsbx($value) . "</p>";
            }
        }

        return $html;
    }

    /**
     * Форматирует информацию о доставке и оплате
     * 
     * @param Order $order Заказ
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @return string HTML-строка
     */
    private static function formatShippingAndPayment(Order $order, $propsCollection): string
    {
        $html = '';

        // Информация о доставке
        $shipmentCollection = $order->getShipmentCollection();
        $shipmentName = '';
        foreach ($shipmentCollection as $shipment) {
            $deliveryName = $shipment->getDeliveryName();
            if ($deliveryName) {
                // Убираем информацию в скобках (например, "Название (ID)")
                $parts = explode('(', $deliveryName);
                $shipmentName = trim($parts[0]);
                break;
            }
        }

        if ($shipmentName) {
            $html .= '<p><strong>Doprava: ' . htmlspecialcharsbx($shipmentName) . '</strong></p>';
        }

        // Стоимость доставки
        $deliveryPrice = $order->getDeliveryPrice();
        if ($deliveryPrice > 0) {
            $formattedPrice = Format::price($deliveryPrice);
            $html .= '<p><strong>Náklady:</strong> ' . $formattedPrice . '</p>';
        }

        // Адрес Zásielkovňa (если есть)
        $zasAdd = self::getProperty($propsCollection, 'zas_add');
        if ($zasAdd) {
            $html .= '<p><strong>Zásielkovňa adresa:</strong> ' . htmlspecialcharsbx($zasAdd->getValue()) . '</p>';
        }

        $zasPlace = self::getProperty($propsCollection, 'zas_place');
        if ($zasPlace) {
            $html .= '<p><strong>Zasielkovňa miesto:</strong> ' . htmlspecialcharsbx($zasPlace->getValue()) . '</p>';
        }

        // Информация об оплате
        $paymentCollection = $order->getPaymentCollection();
        $paymentName = '';
        foreach ($paymentCollection as $payment) {
            $paymentName = $payment->getPaymentSystemName();
            if ($paymentName) {
                break;
            }
        }

        if ($paymentName) {
            $html .= '<p><strong>Platba: ' . htmlspecialcharsbx($paymentName) . '</strong></p>';
        }

        return $html;
    }

    /**
     * Форматирует краткую информацию о заказе для каждого товара
     * Формат: Номер заказа, Имя фамилия, Название товара, размер + количество, служба доставки, способ оплаты
     * Пример: 2516, Kapralova, SIEŤ BEZUZLOVÁ PP OKO 12 CM / HRÚBKA 4 MM / FARBA ZELENÁ,
     * Farba: zelená, 2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks., Packeta, GP WebPay
     * 
     * @param Order $order Заказ
     * @return string Строка с краткой информацией о каждом товаре (разделена переносами строк)
     */
    private static function formatShortOrder(Order $order): string
    {
        $orderNumber = $order->getId();
        $propsCollection = $order->getPropertyCollection();
        
        // Получаем имя и фамилию заказчика (приоритет: LAST_NAME, затем FIO, затем FIO + LAST_NAME)
        $lastName = self::getPropertyValue($propsCollection, 'LAST_NAME');
        $fio = self::getPropertyValue($propsCollection, 'FIO');
        
        if (!empty($lastName)) {
            $customerName = $lastName;
            if (!empty($fio)) {
                $customerName = trim($fio . ' ' . $lastName);
            }
        } elseif (!empty($fio)) {
            $customerName = $fio;
        } else {
            $customerName = self::getPropertyValue($propsCollection, 'EMAIL') ?: 'Customer';
        }
        
        // Получаем название службы доставки
        $shipmentCollection = $order->getShipmentCollection();
        $deliveryName = '';

        foreach ($shipmentCollection as $shipment) {
            $deliveryNameFull = $shipment->getDeliveryName();
            
            if ($deliveryNameFull) {
                // Убираем информацию в скобках (например, "Packeta (ID)")
                $parts = explode('(', $deliveryNameFull);
                $deliveryName = trim($parts[0]);
                break;
            }
        }
        if (empty($deliveryName)) {
            $deliveryName = 'Unknown';
        }
        
        // Получаем способ оплаты
        $paymentCollection = $order->getPaymentCollection();
        $paymentName = '';
        foreach ($paymentCollection as $payment) {
            $paymentName = $payment->getPaymentSystemName();
            if ($paymentName) {
                break;
            }
        }
        if (empty($paymentName)) {
            $paymentName = 'Unknown';
        }
        
        // Формируем строку для каждого товара
        $shortOrderLines = [];
        $basket = $order->getBasket();

        $customerInfo = sprintf('%s, %s', $orderNumber, $customerName);

        $shortOrderLines[] = $customerInfo;

        foreach ($basket as $basketItem) {
            $arMeasure = ProductTable::getCurrentRatioWithMeasure($basketItem->getProductId());
            $basketPropertyCollection = $basketItem->getPropertyCollection();
            $props = $basketPropertyCollection->getPropertyValues();
            
            // Название товара
            $productName = $basketItem->getField('NAME');

            
            // Формируем размер + количество
            // Формат: если есть SIZES_STR - перечень размеров, иначе "количество единица"
            $quantity = $basketItem->getQuantity();
            $measureSymbol = $arMeasure[$basketItem->getProductId()]['MEASURE']['SYMBOL_RUS'] ?? 'ks';
            
            $sizeAndQuantity = '';

            if (!empty($props['SIZES_STR']['VALUE'])) {
                // Формат: "2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks."
                $sizeAndQuantity = Format::sizes($props['SIZES_STR']['VALUE']);
            } else {
                // Формат: "1 ks" (количество единица)
                $sizeAndQuantity = Format::number($quantity) . ' ' . $measureSymbol;
            }

            $color = 'Farba: '. (!empty($props['COLOR']['VALUE']) ? $props['COLOR']['VALUE'] : '-');
            
            // Формируем строку: Номер заказа, Имя фамилия, Название товара, размер + количество, служба доставки, способ оплаты
            $shortOrderLine = sprintf(
                '%s, %s, %s',
                $productName,
                $color,
                $sizeAndQuantity
            );

            $shortOrderLines[] = str_replace(["\r", "\n"], "", $shortOrderLine);
        }

        $orderInfo = sprintf('%s, %s', $deliveryName, $paymentName);

        $shortOrderLines[] = $orderInfo;

        return implode(", ", $shortOrderLines);
    }

    /**
     * Получает значение свойства по коду
     *
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @param string $code Код свойства
     * @return string Значение свойства или пустая строка
     */
    private static function getPropertyValue($propsCollection, string $code): string
    {
        return PropertyHelper::getPropertyByCodeClear($propsCollection, $code);
    }

    /**
     * Получает объект свойства по коду
     * 
     * @param \Bitrix\Sale\PropertyValueCollection $propsCollection Коллекция свойств
     * @param string $code Код свойства
     * @return \Bitrix\Sale\PropertyValue|null Объект свойства или null
     */
    private static function getProperty($propsCollection, string $code)
    {
        return PropertyHelper::getPropertyByCode($propsCollection, $code);
    }
}

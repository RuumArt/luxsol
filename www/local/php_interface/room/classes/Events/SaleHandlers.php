<?php

namespace Room\Events;

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\DiscountCouponTable;

use Room\Helpers\PropertyHelper;
use Room\Services\PscService;

use Room\Tools;

class SaleHandlers
{
    const IB_CATALOG = IBLOCK_ID__CATALOG;

    // Свойства заказа с почтовым индексом: основной адрес и адрес доставки
    const ZIP_PROPERTY_CODES = ['PSС', 'PSС_2'];

    // Пометка в комментарии к заказу, если индекса нет в реестре пошты
    const ZIP_UNKNOWN_MARK = 'PSČ вне справочника почты: ';
    
    // ID товаров, при наличии которых генерируется промокод
    const PROMOCODE_PRODUCT_IDS = [48, 12];

    // Соответствие суммы промокода и ID существующей скидки
    const PROMOCODE_DISCOUNT_MAP = [
        20000 => 27,
        30000 => 28,
        40000 => 29,
        50000 => 30,
        100000 => 31,
    ];

    /**
     * Метод проставляет признака "Sale" у товара при наличии скидочной цены
     *
     * @param $arFields
     * @return bool
     */
    public static function onUpdateProductSale($id, $arFields, $bb)
    {
        $isSale = false;

        /* Добавить проверку на цену */

        $priceData = \CPrice::GetByID($id["ID"]);
        $elementId = $priceData['PRODUCT_ID'];

        if(!empty($elementId)) {
            $productId = $elementId;

            $res = \CCatalogSKU::getOffersList([$productId], self::IB_CATALOG, [], ['ID']);

            if (empty($res[$productId])) {
                return true;
            }

            $mainOffer = array_shift($res[$productId]);

            if (!empty($mainOffer)) {
                $rsPrice = \Bitrix\Catalog\Model\Price::getList(array(
                    'filter' => [
                        'CATALOG_GROUP.ID' => [MAIN_PRICE_ID, SALE_PRICE_ID],
                        'PRODUCT_ID' => $mainOffer["ID"]
                    ],
                ));

                $prices = [];

                while ($arPrice = $rsPrice->fetch()) {
                    $prices[$arPrice["CATALOG_GROUP_ID"]] = $arPrice;
                }

                if (!empty($prices[SALE_PRICE_ID])) {
                    if ((float)$prices[SALE_PRICE_ID]["PRICE"] !== (float)$prices[MAIN_PRICE_ID]["PRICE"]) {
                        $isSale = true;
                    }
                }

                $db_enum_list = \CIBlockProperty::GetPropertyEnum("ATT_IS_SALE", [], ["IBLOCK_ID" => self::IB_CATALOG]);
                $arrProp = [];

                while ($ar_enum_list = $db_enum_list->GetNext()) {
                    $arrProp[] = $ar_enum_list;
                }

                $currentEnumKey = array_search($isSale ? 'Да' : 'Нет', array_column($arrProp, "VALUE"));

                \CIBlockElement::SetPropertyValuesEx($productId, self::IB_CATALOG, ['ATT_IS_SALE' => $arrProp[$currentEnumKey]["ID"]]);
            }
        }
    }

    /**
     * Обработчик события создания заказа
     * Генерирует промокод при наличии товаров 48 и 12
     *
     * @param \Bitrix\Main\Event|Order $eventOrOrder
     * @return void
     */
    public static function onOrderSaved($eventOrOrder)
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        $order = null;
        
        // Проверяем, что передано - Event или Order
        if ($eventOrOrder instanceof \Bitrix\Main\Event) {
            $order = $eventOrOrder->getParameter('ENTITY');
        } elseif ($eventOrOrder instanceof Order) {
            $order = $eventOrOrder;
        }
        
        if (!$order instanceof Order) {
            return;
        }

        // Проверяем наличие товаров и генерируем промокод
        self::checkAndGeneratePromocode($order);
    }

    /**
     * Обработчик события оплаты заказа
     * Генерирует промокод при наличии товаров 48 и 12
     *
     * @param \Bitrix\Main\Event|Order $eventOrOrder
     * @return void
     */
    public static function onOrderPaid($eventOrOrder)
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        $order = null;
        $oldValues = null;
        
        // Проверяем, что передано - Event или Order
        if ($eventOrOrder instanceof \Bitrix\Main\Event) {
            $order = $eventOrOrder->getParameter("ENTITY");
            $oldValues = $eventOrOrder->getParameter("VALUES");
        } elseif ($eventOrOrder instanceof Order) {
            $order = $eventOrOrder;
            // Если передан Order напрямую, проверяем статус оплаты через историю
            // или просто проверяем текущий статус
        }
        
        if (!$order instanceof Order) {
            return;
        }

        if (!$order->isPaid()) return;

        // Проверяем, что заказ оплачен
        // Если есть старые значения, проверяем изменение статуса
        // if ($oldValues !== null && isset($oldValues['PAYED'])) {
        //     // Проверяем, что заказ только что был оплачен (был не оплачен, стал оплачен)

        //     if ($oldValues['PAYED'] == 'Y' || $order->getField('PAYED') != 'Y') {
        //         return;
        //     }
        // }

        /* else {
            // Если нет старых значений, просто проверяем текущий статус оплаты
            if ($order->getField('PAYED') != 'Y') {
                return;
            }
        } */

        // Проверяем наличие товаров и генерируем промокод
        self::checkAndGeneratePromocode($order);
    }

    /**
     * Проверяет наличие товаров 48 и 12 в заказе и генерирует промокод
     *
     * @param Order $order
     * @return void
     */
    protected static function checkAndGeneratePromocode(Order $order)
    {
        $orderId = $order->getId();
        
        // Проверяем, был ли уже сгенерирован промокод для этого заказа
        // Используем свойство заказа для хранения флага
        $propertyCollection = $order->getPropertyCollection();
        
        // Ищем свойство по коду
        $promocodeSentProperty = null;
        foreach ($propertyCollection as $item) {
            if ($item->getField('CODE') === 'PROMOCODE_SENT') {
                $promocodeSentProperty = $item;
                break;
            }
        }
        
        // Если свойство существует и промокод уже был отправлен, выходим
        if ($promocodeSentProperty && $promocodeSentProperty->getValue() === 'Y') {
            return;
        }

        $basket = $order->getBasket();
        
        if (!$basket) {
            return;
        }

        $promoProductSum = 0;
        $hasPromoProducts = false;

        // Проверяем наличие товаров с ID 48 и 12
        foreach ($basket as $basketItem) {
            $productId = $basketItem->getProductId();
            
            if (in_array($productId, self::PROMOCODE_PRODUCT_IDS)) {
                $hasPromoProducts = true;
                $price = $basketItem->getPrice();
                $quantity = $basketItem->getQuantity();
                $promoProductSum = $price * $quantity;
            }
        }

        // Логируем для отладки
        if (function_exists('AddMessage2Log')) {
            AddMessage2Log('Проверка промокода для заказа #' . $orderId . ': товары найдены=' . ($hasPromoProducts ? 'да' : 'нет') . ', сумма=' . $promoProductSum, 'sale');
        }

        // Если товары найдены, генерируем промокод
        if ($hasPromoProducts && $promoProductSum > 0) {
            $userId = $order->getUserId();
            $userEmail = self::getUserEmail($userId);
            
            if (!$userEmail) {
                if (function_exists('AddMessage2Log')) {
                    AddMessage2Log('Email пользователя не найден для заказа #' . $orderId, 'sale');
                }
                return;
            }
            
            $couponCode = self::generatePromocode($promoProductSum, $userId, $orderId);
            
            if ($couponCode) {

                $emailSent = self::sendPromocodeEmail($userEmail, $couponCode, $promoProductSum, $order);
                
                // Если письмо отправлено успешно, помечаем заказ
                if ($emailSent) {
                    self::markPromocodeSent($order);
                    if (function_exists('AddMessage2Log')) {
                        AddMessage2Log('Промокод отправлен пользователю: ' . $couponCode, 'sale');
                    }
                } else {
                    if (function_exists('AddMessage2Log')) {
                        AddMessage2Log('Ошибка отправки письма с промокодом: ' . $couponCode, 'sale');
                    }
                }
            } else {
                if (function_exists('AddMessage2Log')) {
                    AddMessage2Log('Не удалось сгенерировать промокод для заказа #' . $orderId, 'sale');
                }
            }
        }
    }

    /**
     * Получает ID свойства заказа для хранения флага отправки промокода
     * Создает свойство, если его нет
     *
     * @return int
     */
    protected static function getPromocodeSentPropertyId()
    {
        if (!Loader::includeModule('sale')) {
            return 0;
        }

        // Ищем существующее свойство (берем первое найденное)
        $property = \Bitrix\Sale\Internals\OrderPropsTable::getList([
            'filter' => ['CODE' => 'PROMOCODE_SENT'],
            'limit' => 1
        ])->fetch();

        if ($property) {
            return $property['ID'];
        }

        // Получаем все типы плательщиков
        $personTypes = \Bitrix\Sale\Internals\PersonTypeTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'select' => ['ID']
        ])->fetchAll();

        if (empty($personTypes)) {
            return 0;
        }

        // Создаем свойство для каждого типа плательщика
        $firstPropertyId = 0;
        
        foreach ($personTypes as $personType) {
            $result = \Bitrix\Sale\Internals\OrderPropsTable::add([
                'PERSON_TYPE_ID' => $personType['ID'],
                'NAME' => 'Промокод отправлен',
                'CODE' => 'PROMOCODE_SENT',
                'TYPE' => 'Y/N',
                'REQUIRED' => 'N',
                'SORT' => 1000,
                'USER_PROPS' => 'N', // Скрытое свойство, не отображается пользователю
            ]);

            if ($result->isSuccess() && $firstPropertyId === 0) {
                $firstPropertyId = $result->getId();
            }
        }

        return $firstPropertyId;
    }

    /**
     * Помечает заказ как обработанный (промокод отправлен)
     *
     * @param Order $order
     * @return void
     */
    protected static function markPromocodeSent(Order $order)
    {
        try {
            $propertyCollection = $order->getPropertyCollection();
            
            // Ищем свойство по коду, а не по ID (так как ID может отличаться для разных типов плательщиков)
            $property = null;
            
            foreach ($propertyCollection as $item) {
                if ($item->getField('CODE') === 'PROMOCODE_SENT') {
                    $property = $item;
                    break;
                }
            }
            
            if (!$property) {
                // Создаем новое свойство в коллекции
                // Находим ID свойства для типа плательщика заказа
                $personTypeId = $order->getPersonTypeId();
                $orderProperty = \Bitrix\Sale\Internals\OrderPropsTable::getList([
                    'filter' => [
                        'CODE' => 'PROMOCODE_SENT',
                        'PERSON_TYPE_ID' => $personTypeId
                    ],
                    'limit' => 1
                ])->fetch();
                
                if ($orderProperty) {
                    $property = $propertyCollection->createItem();
                    $property->setField('ORDER_PROPS_ID', $orderProperty['ID']);
                }
            }
            
            if ($property) {
                $property->setValue('Y');
                $order->save();
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Ошибка при пометке промокода: ' . $e->getMessage(), 'sale');
            }
        }
    }

    /**
     * Генерирует промокод с указанной суммой
     *
     * @param float $amount Сумма промокода
     * @param int $userId ID пользователя
     * @param int $orderId ID заказа
     * @return string|false Код промокода или false при ошибке
     */
    protected static function generatePromocode($amount, $userId, $orderId = 0)
    {
        if (!Loader::includeModule('sale')) {
            return false;
        }

        // Генерируем уникальный код промокода
        $couponCode = null;
        $attempts = 0;
        $maxAttempts = 10; // Увеличиваем количество попыток
        
        while ($attempts < $maxAttempts) {
            $couponCode = 'PROMO' . strtoupper(substr(md5($userId . $orderId . time() . rand(1000, 9999) . microtime(true) . $attempts), 0, 8));
            
            $checkCoupon = DiscountCouponTable::getList([
                'filter' => ['COUPON' => $couponCode],
                'limit' => 1
            ])->fetch();
            
            if (!$checkCoupon) {
                break; // Код уникален
            }
            
            $attempts++;
        }
        
        // Если после всех попыток код не уникален, возвращаем false
        if ($attempts >= $maxAttempts || !$couponCode) {
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Не удалось сгенерировать уникальный промокод после ' . $maxAttempts . ' попыток', 'sale');
            }
            return false;
        }

        // Получаем ID существующей скидки по сумме
        $discountId = self::getDiscountIdByAmount($amount);
        
        if (!$discountId) {
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Не найдена скидка для суммы промокода: ' . $amount, 'sale');
            }
            return false;
        }
        
        $fields = [
            'DISCOUNT_ID' => $discountId,
            'LID' => "s1", // Признак сайта
            'ACTIVE' => 'Y',
            'COUPON' => $couponCode,
            'TYPE' => 2,
            'USE_COUNT' => 0,
            'DESCRIPTION' => 'Промокод на сумму ' . number_format($amount, 2, '.', '') . ' руб. (заказ #' . $orderId . ')',
        ];

        // Пытаемся создать купон через D7 API
        $result = DiscountCouponTable::add($fields);
        
        if ($result->isSuccess()) {
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Промокод создан успешно: ' . $couponCode . ' (скидка ID: ' . $discountId . ', сумма: ' . $amount . ' руб.)', 'sale');
            }
            return $couponCode;
        } else {
            // Логируем ошибки
            $errors = $result->getErrorMessages();
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Ошибка создания промокода через D7: ' . implode(', ', $errors), 'sale');
            }
            return false;
        }
    }

    /**
     * Получает ID существующей скидки по сумме промокода
     *
     * @param float $amount Сумма промокода
     * @return int|false ID скидки или false
     */
    protected static function getDiscountIdByAmount($amount)
    {
        // Проверяем точное соответствие суммы
        if (isset(self::PROMOCODE_DISCOUNT_MAP[$amount])) {
            $discountId = self::PROMOCODE_DISCOUNT_MAP[$amount];
            
            // Проверяем, что скидка существует и активна
            $discount = \Bitrix\Sale\Internals\DiscountTable::getList([
                'filter' => [
                    'ID' => $discountId,
                    'ACTIVE' => 'Y'
                ],
                'select' => ['ID', 'NAME', 'ACTIVE'],
                'limit' => 1
            ])->fetch();
            
            if ($discount) {
                return $discountId;
            } else {
                if (function_exists('AddMessage2Log')) {
                    AddMessage2Log('Скидка с ID ' . $discountId . ' не найдена или неактивна', 'sale');
                }
            }
        } else {
            if (function_exists('AddMessage2Log')) {
                AddMessage2Log('Не найдено соответствие для суммы промокода: ' . $amount . '. Доступные суммы: ' . implode(', ', array_keys(self::PROMOCODE_DISCOUNT_MAP)), 'sale');
            }
        }
        
        return false;
    }

    /**
     * Получает email пользователя
     *
     * @param int $userId
     * @return string|false
     */
    protected static function getUserEmail($userId)
    {
        if (!$userId) {
            return false;
        }

        $user = \CUser::GetByID($userId)->Fetch();
        
        return $user && !empty($user['EMAIL']) ? $user['EMAIL'] : false;
    }

    /**
     * Отправляет письмо с промокодом
     *
     * @param string $email Email получателя
     * @param string $couponCode Код промокода
     * @param float $amount Сумма промокода
     * @param Order $order Заказ
     * @return bool
     */
    protected static function sendPromocodeEmail($email, $couponCode, $amount, Order $order)
    {
        if (!Loader::includeModule('main')) {
            return false;
        }

        $orderId = $order->getId();
        $orderDate = $order->getDateInsert()->format('d.m.Y H:i');
        $orderPrice = $order->getPrice();
        
        $arEventFields = [
            'SALE_EMAIL' => "noreply@triptyque.ru",
            'EMAIL' => $email,
            'COUPON_CODE' => $couponCode,
            'COUPON_AMOUNT' => number_format($amount, 2, '.', ' '),
            'ORDER_ID' => $orderId,
            'ORDER_DATE' => $orderDate,
            'ORDER_PRICE' => number_format($orderPrice, 2, '.', ' '),
        ];

        // Отправляем письмо через почтовое событие
        $result = \CEvent::Send('PROMOCODE_SEND_TO_USER', 's1', $arEventFields, 'N', 63);
        
        return $result !== false;
    }

    public static function OnSaleStatusOrderChange($entity)
    {
        $order = $entity;
        $status = $order->getField('STATUS_ID');

        // В DPD уходят только заказы с курьерской доставкой DPD. Раньше
        // проверки не было, и накладная создавалась для любого заказа
        // в статусе RE - в том числе для самовывоза и Packeta
        if ($status === 'RE' && Tools\Order::isDpdOrder($order)) {
            Tools\Order::createDpd($order);
        }

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS
        );
    }

    /**
     * Приведение почтового индекса к пяти цифрам перед сохранением заказа
     *
     * Маска в форме оформления пишет индекс с пробелом ("831 02"), поэтому
     * в базе он так и лежал, а DPD такой формат не принимает. Нормализуем
     * в одном месте: сюда попадают и заказы с сайта, и правки из админки
     *
     * Обработчик подключён через AddEventHandler, а в таком режиме Битрикс
     * передаёт не объект события, а его параметры по порядку: первым идёт
     * сам заказ. Строгий тип \Bitrix\Main\Event здесь ронял каждое
     * сохранение заказа с ошибкой 500, поэтому принимаем оба варианта -
     * так же, как onOrderSaved
     *
     * Нормализация индекса - удобство, а не условие приёма заказа: любая
     * ошибка внутри пишется в лог, и заказ сохраняется как есть
     *
     * @param \Bitrix\Main\Event|Order $eventOrOrder
     * @return \Bitrix\Main\EventResult
     */
    public static function OnSaleOrderBeforeSaved($eventOrOrder)
    {
        $order = $eventOrOrder instanceof \Bitrix\Main\Event
            ? $eventOrOrder->getParameter('ENTITY')
            : $eventOrOrder;

        if ($order instanceof Order) {
            try {
                self::normalizeZipProperties($order);
            } catch (\Throwable $e) {
                AddMessage2Log(
                    'Нормализация PSČ пропущена: ' . $e->getMessage()
                        . ' в ' . $e->getFile() . ':' . $e->getLine(),
                    'room_sale'
                );
            }
        }

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS
        );
    }

    /**
     * Чистит индексы заказа и помечает те, которых нет в реестре пошты
     *
     * Заказ не блокируется: справочник обновляется раз в месяц, и новый
     * индекс может в нём ещё отсутствовать. Пометка нужна менеджеру,
     * чтобы он проверил адрес до перевода заказа в "Готово к отправке"
     *
     * @param Order $order
     * @return void
     */
    private static function normalizeZipProperties(Order $order): void
    {
        $collection = $order->getPropertyCollection();

        if (!$collection) {
            return;
        }

        $unknown = [];

        foreach (self::ZIP_PROPERTY_CODES as $code) {
            $property = PropertyHelper::getPropertyByCode($collection, $code);

            if (!$property) {
                continue;
            }

            $value = trim((string)$property->getValue());

            if ($value === '') {
                continue;
            }

            $digits = PscService::normalize($value);

            // Индекс с буквами и опечатками не трогаем: пусть менеджер
            // увидит исходное значение, а не обрезок из него
            if (strlen($digits) !== 5) {
                continue;
            }

            if ($digits !== $value) {
                $property->setValue($digits);
            }

            if (PscService::isReady() && !PscService::exists($digits)) {
                $unknown[] = PscService::format($digits);
            }
        }

        if (empty($unknown)) {
            return;
        }

        $mark = self::ZIP_UNKNOWN_MARK . implode(', ', array_unique($unknown));
        $comments = (string)$order->getField('COMMENTS');

        // Заказ сохраняется много раз, пометку добавляем только однажды
        if (mb_strpos($comments, $mark) !== false) {
            return;
        }

        $order->setField('COMMENTS', trim($comments . "\n" . $mark));
    }
}

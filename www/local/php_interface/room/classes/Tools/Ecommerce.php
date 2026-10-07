<?php

namespace Room\Tools;

use Bitrix\Main\Application;
use Bitrix\Sale\Order;

/**
 * Данные о покупке для аналитики
 *
 * Google Analytics и Google Ads узнают о заказе из события purchase
 * в dataLayer. Контейнер GTM уже настроен на это имя события, поэтому
 * задача шаблона - положить туда правильные данные ровно один раз.
 *
 * Страницу «заказ принят» покупатель нередко перезагружает или открывает
 * из письма, поэтому отправку помечаем в сессии: иначе один заказ
 * посчитается несколько раз и выручка в отчётах окажется завышенной
 */
class Ecommerce
{
    /**
     * Ключ в сессии со списком заказов, о которых уже сообщили
     */
    const SESSION_KEY = 'ROOM_GA_PURCHASE_SENT';

    /**
     * Данные покупки для dataLayer
     *
     * @param Order $order
     * @return array|null null - об этом заказе уже сообщали
     */
    public static function purchasePayload(Order $order): ?array
    {
        $orderId = (int)$order->getId();

        if ($orderId <= 0 || self::alreadySent($orderId)) {
            return null;
        }

        $items = [];

        foreach ($order->getBasket() as $basketItem) {
            $props = $basketItem->getPropertyCollection()->getPropertyValues();

            // Артикул понятнее в отчётах, чем внутренний ID
            $code = trim((string)($props['ARTICUL']['VALUE'] ?? ''));

            $items[] = [
                'item_id' => $code !== '' ? $code : (string)$basketItem->getProductId(),
                'item_name' => (string)$basketItem->getField('NAME'),
                'price' => round((float)$basketItem->getPrice(), 2),
                'quantity' => (float)$basketItem->getQuantity(),
            ];
        }

        if (empty($items)) {
            return null;
        }

        self::markSent($orderId);

        $number = trim((string)$order->getField('ACCOUNT_NUMBER'));

        return [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $number !== '' ? $number : (string)$orderId,
                'value' => round((float)$order->getPrice(), 2),
                'tax' => round((float)$order->getField('TAX_VALUE'), 2),
                'shipping' => round((float)$order->getDeliveryPrice(), 2),
                'currency' => (string)$order->getCurrency(),
                'items' => $items,
            ],
        ];
    }

    /**
     * @param int $orderId
     * @return bool
     */
    private static function alreadySent(int $orderId): bool
    {
        return in_array($orderId, self::sentOrders(), true);
    }

    /**
     * @param int $orderId
     * @return void
     */
    private static function markSent(int $orderId): void
    {
        $sent = self::sentOrders();
        $sent[] = $orderId;

        // Храним только последние заказы: список нужен на время сессии
        $sent = array_slice(array_unique($sent), -20);

        $session = self::session();

        if ($session !== null) {
            $session->set(self::SESSION_KEY, $sent);

            return;
        }

        $_SESSION[self::SESSION_KEY] = $sent;
    }

    /**
     * @return array
     */
    private static function sentOrders(): array
    {
        $session = self::session();

        $raw = $session !== null
            ? $session->get(self::SESSION_KEY)
            : ($_SESSION[self::SESSION_KEY] ?? []);

        return is_array($raw) ? array_map('intval', $raw) : [];
    }

    /**
     * Сессия Битрикса. На старых сборках объекта нет - тогда работаем
     * с $_SESSION напрямую
     *
     * @return \Bitrix\Main\Session\SessionInterface|null
     */
    private static function session()
    {
        $application = Application::getInstance();

        if (!$application || !method_exists($application, 'getSession')) {
            return null;
        }

        $session = $application->getSession();

        return $session && $session->isStarted() ? $session : null;
    }
}

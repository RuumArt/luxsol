<?php

namespace Room\Tools;

use Bitrix\Main\Application;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;

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
    const PAYMENT_RETURN_KEY = 'ROOM_GA_PAYMENT_RETURN';

    /** The callback may nominate an order; only a saved paid order can be sent. */
    public static function rememberPaymentReturn(int $orderId): void
    {
        if ($orderId > 0) {
            try {
                self::setSessionValue(self::PAYMENT_RETURN_KEY, $orderId);
            } catch (\Throwable $e) {
                // Analytics storage must not turn a verified payment into an error.
            }
        }
    }

    public static function paymentReturnPayload(): ?array
    {
        $orderId = (int)self::sessionValue(self::PAYMENT_RETURN_KEY);
        if ($orderId <= 0) {
            return null;
        }
        self::setSessionValue(self::PAYMENT_RETURN_KEY, null);
        $order = Order::load($orderId);
        return $order && $order->isPaid() ? self::purchasePayload($order) : null;
    }

    public static function requiresConfirmedPayment(Order $order): bool
    {
        foreach ($order->getPaymentCollection() as $payment) {
            if ((float)$payment->getSum() <= 0) {
                continue;
            }
            $paySystem = Manager::getById($payment->getPaymentSystemId());
            if (strtolower(basename(rtrim((string)($paySystem['ACTION_FILE'] ?? ''), '/'))) === 'gpweb') {
                return true;
            }
        }
        return false;
    }

    /** GA4 data, with the checkout event name understood by the published GTM. */
    public static function checkoutPayload(array $rows, string $currency): ?array
    {
        $items = [];
        $value = 0;
        foreach ($rows as $row) {
            $data = $row['data'] ?? [];
            $quantity = (float)($data['QUANTITY'] ?? 0);
            if ($quantity <= 0 || empty($data['PRODUCT_ID'])) {
                continue;
            }
            $article = '';
            foreach (($data['PROPS'] ?? []) as $property) {
                if (($property['CODE'] ?? '') === 'ARTICUL') {
                    $article = trim((string)($property['VALUE'] ?? ''));
                }
            }
            $price = round((float)($data['PRICE'] ?? 0), 2);
            $items[] = [
                'item_id' => $article !== '' ? $article : (string)$data['PRODUCT_ID'],
                'item_name' => html_entity_decode((string)($data['NAME'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'price' => $price,
                'quantity' => $quantity,
            ];
            $value += $price * $quantity;
        }
        return $items ? ['event' => 'checkout', 'ecommerce' => [
            'currency' => $currency, 'value' => round($value, 2), 'items' => $items,
        ]] : null;
    }

    /**
     * Данные покупки для dataLayer
     *
     * @param Order $order
     * @return array|null null - заказ ещё не подходит для учёта или уже отправлен
     */
    public static function purchasePayload(Order $order): ?array
    {
        $orderId = (int)$order->getId();

        if ($orderId <= 0 || $order->isCanceled() ||
            (self::requiresConfirmedPayment($order) && !$order->isPaid()) || self::alreadySent($orderId)) {
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

    private static function sessionValue(string $key)
    {
        $session = self::session();
        return $session !== null ? $session->get($key) : ($_SESSION[$key] ?? null);
    }

    private static function setSessionValue(string $key, $value): void
    {
        $session = self::session();
        if ($session !== null) {
            $session->set($key, $value);
        } else {
            $_SESSION[$key] = $value;
        }
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

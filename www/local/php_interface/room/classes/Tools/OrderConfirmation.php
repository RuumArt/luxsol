<?php

namespace Room\Tools;

use Bitrix\Main\Mail\Internal\EventTable;
use Bitrix\Sale\Order;

class OrderConfirmation
{
    /** Queue success means accepted for sending, not proof of inbox delivery. */
    public static function orderEmailWasSent(Order $order, string $email): bool
    {
        if ($email === '') {
            return false;
        }

        try {
            $events = EventTable::getList([
                'filter' => [
                    '=EVENT_NAME' => 'SALE_NEW_ORDER',
                    '=LID' => $order->getSiteId(),
                    '=SUCCESS_EXEC' => 'Y',
                    '>=DATE_INSERT' => $order->getDateInsert(),
                ],
                'select' => ['C_FIELDS'],
                'order' => ['ID' => 'DESC'],
                'limit' => 200,
            ]);

            while ($event = $events->fetch()) {
                $fields = $event['C_FIELDS'] ?? [];
                $number = (string)($order->getField('ACCOUNT_NUMBER') ?: $order->getId());
                $matches = (string)($fields['ORDER_REAL_ID'] ?? '') === (string)$order->getId()
                    || (string)($fields['ORDER_ID'] ?? '') === $number;
                $recipients = preg_split('/\s*[,;]\s*/', (string)($fields['EMAIL'] ?? ''));

                if ($matches && in_array(strtolower($email), array_map('strtolower', $recipients), true)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Unknown mail state must use the future tense on the confirmation page.
        }

        return false;
    }
}

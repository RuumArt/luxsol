<?php

namespace Room\Tools;

/** Calendar-day reminders in the shop's timezone; never cancels orders or invoices. */
class AdvanceInvoiceReminderPolicy
{
    public const DAYS = [3, 5];
    public const TIMEZONE = 'Europe/Bratislava';

    /** A paid payment also suppresses reminders on partially paid orders. */
    public static function forOrder(array $state, \DateTimeInterface $now): ?int
    {
        $hour = (int)\DateTimeImmutable::createFromInterface($now)
            ->setTimezone(new \DateTimeZone(self::TIMEZONE))->format('G');
        if (($state['status'] ?? '') !== 'OZ' || empty($state['bank_transfer'])
            || !filter_var($state['email'] ?? '', FILTER_VALIDATE_EMAIL)
            || empty($state['status_ts']) || $hour < 9 || $hour >= 18) {
            return null;
        }
        return self::dueDay(new \DateTimeImmutable('@' . (int)$state['status_ts']), $now,
            !empty($state['paid']), !empty($state['cancelled']));
    }

    /**
     * Only the reminder for today's window can be sent. Missed reminders are not
     * accumulated and delivered together after a scheduler outage.
     * $sentDays belongs to this specific invoice, not merely to the order number.
     */
    public static function dueDay(
        \DateTimeInterface $invoiceSentAt,
        \DateTimeInterface $now,
        bool $paid,
        bool $cancelled,
        array $sentDays = []
    ): ?int {
        if ($paid || $cancelled || $invoiceSentAt > $now) {
            return null;
        }

        $timezone = new \DateTimeZone(self::TIMEZONE);
        $start = \DateTimeImmutable::createFromInterface($invoiceSentAt)->setTimezone($timezone)->setTime(0, 0);
        $today = \DateTimeImmutable::createFromInterface($now)->setTimezone($timezone)->setTime(0, 0);
        $day = (int)$start->diff($today)->format('%a');

        if (!in_array($day, self::DAYS, true) || in_array($day, array_map('intval', $sentDays), true)) {
            return null;
        }

        return $day;
    }
}

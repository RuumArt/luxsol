<?php
namespace Room\Tools;

class ProductionUpdatePolicy
{
    public const TIMEZONE = 'Europe/Bratislava';
    public const FINISHED_STATUSES = ['RE', 'LP', 'OT', 'F'];

    /** Order date is day zero. Weekends, but not public holidays, are excluded. */
    public static function dueAt(\DateTimeInterface $created): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromInterface($created)
            ->setTimezone(new \DateTimeZone(self::TIMEZONE))->setTime(10, 0);
        for ($days = 0; $days < 3;) {
            $date = $date->modify('+1 day');
            if ((int)$date->format('N') <= 5) { $days++; }
        }
        return $date;
    }

    /** Late prepayment moves the one-off notification to the next available weekday at 10. */
    public static function scheduledAt(array $state): \DateTimeImmutable
    {
        $due = self::dueAt(new \DateTimeImmutable('@' . (int)$state['created_ts']));
        $paidAt = (int)($state['prepayment_paid_ts'] ?? 0);
        if ($paidAt <= $due->getTimestamp()) { return $due; }

        $paid = (new \DateTimeImmutable('@' . $paidAt))->setTimezone(new \DateTimeZone(self::TIMEZONE));
        $next = $paid->setTime(10, 0);
        if ($next <= $paid) { $next = $next->modify('+1 day'); }
        while ((int)$next->format('N') > 5) { $next = $next->modify('+1 day'); }
        return $next;
    }

    public static function eligible(array $state, array $config, \DateTimeInterface $now): bool
    {
        if (empty($state['created_ts']) || empty($state['custom_net']) || !empty($state['cancelled'])
            || ($state['site'] ?? '') !== 's1' || !filter_var($state['email'] ?? '', FILTER_VALIDATE_EMAIL)
            || in_array($state['status'] ?? '', self::FINISHED_STATUSES, true)
            || (int)$state['created_ts'] < (int)($config['active_since'] ?? PHP_INT_MAX)) {
            return false;
        }
        // The timer starts at creation, not at a status transition. Waiting statuses
        // are only a filter: a paid OZ need not be moved back to N to receive mail.
        if (!in_array($state['status'] ?? '', $config['pending_statuses'] ?? [], true)
            || empty($state['payment_ready'])) { return false; }
        // An invoice-sent status always requires the actual paid marker, including
        // an accidentally retained COD method on that order.
        if (($state['status'] ?? '') === 'OZ' && empty($state['paid'])) { return false; }
        $due = self::scheduledAt($state);
        $local = \DateTimeImmutable::createFromInterface($now)->setTimezone(new \DateTimeZone(self::TIMEZONE));
        return $local >= $due && $local < $due->modify('+1 hour');
    }
}

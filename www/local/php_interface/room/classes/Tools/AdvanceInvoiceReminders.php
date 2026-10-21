<?php
namespace Room\Tools;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event as MailEvent;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\OrderTable;

/** Immediate Bitrix mail dispatch with a persistent, at-most-once journal. */
class AdvanceInvoiceReminders
{
    public const OPTION = 'luxsol_invoice_reminders';
    private static array $dispatch = [];
    private static bool $blocked = false;

    protected static function now(): \DateTimeImmutable { return new \DateTimeImmutable(); }

    public static function config(): array
    {
        return json_decode(Option::get('main', self::OPTION, '{}'), true) ?: [];
    }

    public static function snapshot(int $orderId, int $paySystemId): ?array
    {
        // Fresh database fields prevent an old order object hiding a recent payment.
        $row = OrderTable::getById($orderId)->fetch();
        $order = $row ? Order::load($orderId) : null;
        if (!$order || !$row['DATE_STATUS']) {
            return null;
        }
        $bank = true;
        $hasPayment = false;
        $paid = $row['PAYED'] === 'Y';
        foreach ($order->getPaymentCollection() as $payment) {
            if ((float)$payment->getSum() <= 0) {
                continue;
            }
            $hasPayment = true;
            $paid = $paid || $payment->isPaid();
            if ((int)$payment->getPaymentSystemId() !== $paySystemId || $payment->isInner()) {
                $bank = false;
            }
        }
        $email = $order->getPropertyCollection()->getUserEmail();
        return [
            'id'=>$orderId, 'number'=>(string)($row['ACCOUNT_NUMBER'] ?: $orderId),
            'site'=>$row['LID'], 'status'=>$row['STATUS_ID'],
            'status_ts'=>$row['DATE_STATUS']->getTimestamp(), 'paid'=>$paid,
            'cancelled'=>$row['CANCELED'] === 'Y', 'bank_transfer'=>$bank && $hasPayment,
            'email'=>$email ? trim((string)$email->getValue()) : '',
        ];
    }

    public static function key(array $state, int $day): string
    {
        return hash('sha256', $state['id'] . ':' . $state['status_ts'] . ':' . $day);
    }

    /** Rechecked before compilation and immediately before the mail transport. */
    private static function allowed(): bool
    {
        $config = self::config();
        $context = self::$dispatch;
        if (empty($config['enabled']) || !$context) {
            return false;
        }
        $state = self::snapshot($context['id'], (int)$config['pay_system_id']);
        if (!$state || $state['site'] !== 's1') {
            return false;
        }
        $day = AdvanceInvoiceReminderPolicy::forOrder($state, static::now());
        $log = AdvanceInvoiceReminderLogTable::getById($context['key'])->fetch();
        return $day === $context['day'] && self::key($state, $day) === $context['key']
            && strcasecmp($state['email'], $context['email']) === 0
            && ($log['RESULT'] ?? '') === 'processing';
    }

    public static function guardEvent(&$fields, &$template): ?bool
    {
        if (($template['EVENT_NAME'] ?? '') !== AdvanceInvoiceReminderTemplate::EVENT_NAME) {
            return null;
        }
        $config = self::config();
        if ((int)($template['ID'] ?? 0) !== (int)($config['template_id'] ?? 0)
            || !self::allowed() || (string)($fields['EMAIL'] ?? '') !== self::$dispatch['email']
            || (string)($fields['ORDER_NUMBER'] ?? '') !== self::$dispatch['number']) {
            self::$blocked = true;
            return false;
        }
        return null;
    }

    public static function guardTransport(\Bitrix\Main\Event $event): ?\Bitrix\Main\EventResult
    {
        $params = $event->getParameter(0);
        if (($params['HEADER']['X-Luxsol-Notification'] ?? '') !== 'advance-invoice-reminder') {
            return null;
        }
        if (!self::allowed()) {
            self::$blocked = true;
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR);
        }
        return null;
    }

    public static function run(bool $send = false): array
    {
        $config = self::config();
        if (empty($config['template_id']) || empty($config['pay_system_id'])) {
            throw new \RuntimeException('Install the invoice reminder service first.');
        }
        if ($send && empty($config['enabled'])) {
            throw new \RuntimeException('Automatic invoice reminders are disabled.');
        }
        $connection = Application::getConnection();
        if (!$connection->lock('luxsol-invoice-reminders', 0)) {
            return ['locked'=>true];
        }
        $result = ['checked_at'=>static::now()->format(DATE_ATOM), 'enabled'=>!empty($config['enabled']), 'preview'=>!$send, 'eligible'=>[], 'accepted'=>[], 'failed'=>[], 'blocked'=>[], 'already_processed'=>[]];
        try {
            $now = static::now();
            $rows = OrderTable::getList([
                'filter'=>['=STATUS_ID'=>'OZ', '=PAYED'=>'N', '=CANCELED'=>'N', '=LID'=>'s1',
                    '>=DATE_STATUS'=>DateTime::createFromTimestamp($now->modify('-6 days')->getTimestamp())],
                'select'=>['ID'], 'order'=>['DATE_STATUS'=>'ASC'],
            ]);
            while ($row = $rows->fetch()) {
                $state = self::snapshot((int)$row['ID'], (int)$config['pay_system_id']);
                $day = $state ? AdvanceInvoiceReminderPolicy::forOrder($state, $now) : null;
                if ($day === null) {
                    continue;
                }
                $key = self::key($state, $day);
                if (AdvanceInvoiceReminderLogTable::getById($key)->fetch()) {
                    $result['already_processed'][] = $state['id'];
                    continue;
                }
                $result['eligible'][] = ['order'=>$state['id'], 'day'=>$day];
                if (!$send) {
                    continue;
                }
                // Reservation survives a crash after SMTP acceptance, preventing duplicates.
                $added = AdvanceInvoiceReminderLogTable::add([
                    'ID'=>$key, 'ORDER_ID'=>$state['id'], 'STATUS_TS'=>$state['status_ts'],
                    'REMINDER_DAY'=>$day, 'RESULT'=>'processing', 'CREATED_AT'=>new DateTime(),
                ]);
                if (!$added->isSuccess()) {
                    throw new \RuntimeException('Cannot reserve reminder dispatch.');
                }
                self::$dispatch = $state + ['key'=>$key, 'day'=>$day];
                self::$blocked = false;
                try {
                    $status = MailEvent::sendImmediate([
                        'EVENT_NAME'=>AdvanceInvoiceReminderTemplate::EVENT_NAME,
                        'LID'=>$state['site'], 'MESSAGE_ID'=>(int)$config['template_id'],
                        'C_FIELDS'=>['EMAIL'=>$state['email'], 'ORDER_NUMBER'=>$state['number']], 'DUPLICATE'=>'N',
                    ]);
                    $outcome = self::$blocked ? 'blocked' : ($status === MailEvent::SEND_RESULT_SUCCESS ? 'accepted' : 'failed');
                } catch (\Throwable $error) {
                    $outcome = 'failed';
                } finally {
                    self::$dispatch = [];
                }
                $updated = AdvanceInvoiceReminderLogTable::update($key, ['RESULT'=>$outcome]);
                if (!$updated->isSuccess()) {
                    throw new \RuntimeException('Cannot record reminder outcome; reservation retained.');
                }
                $result[$outcome][] = $state['id'];
            }
        } finally {
            self::$dispatch = [];
            $connection->unlock('luxsol-invoice-reminders');
        }
        return $result;
    }
}

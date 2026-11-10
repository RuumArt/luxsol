<?php
namespace Room\Tools;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event as MailEvent;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\OrderTable;

/** Immediate dispatch with fresh guards and at-most-once reservation per order. */
class ProductionUpdates
{
    public const OPTION = 'luxsol_production_updates';
    private static array $dispatch = [];
    private static bool $blocked = false;
    protected static function now(): \DateTimeImmutable { return new \DateTimeImmutable(); }
    public static function config(): array { return json_decode(Option::get('main', self::OPTION, '{}'), true) ?: []; }

    public static function snapshot(int $id): ?array
    {
        $row = OrderTable::getById($id)->fetch();
        $order = $row ? Order::load($id) : null;
        if (!$order || !$row['DATE_INSERT']) { return null; }
        $email = $order->getPropertyCollection()->getUserEmail();
        $net = false;
        foreach ($order->getBasket() as $item) {
            $props = $item->getPropertyCollection()->getPropertyValues();
            if (Format::sizeLines((string)($props['SIZES_STR']['VALUE'] ?? '')) !== []) { $net = true; break; }
        }
        $codIds = array_map('intval', self::config()['cod_pay_system_ids'] ?? []);
        $hasPayment = false;
        $prepaymentOutstanding = false;
        $requiresPrepayment = false;
        $prepaymentPaidAt = 0;
        foreach ($order->getPaymentCollection() as $payment) {
            if ((float)$payment->getSum() <= 0) { continue; }
            $hasPayment = true;
            // Unknown and mixed unpaid prepayment methods are blocked, even if
            // another part of the order uses cash on delivery.
            if ($payment->isInner() || !in_array((int)$payment->getPaymentSystemId(), $codIds, true)) {
                $requiresPrepayment = true;
                if (!$payment->isPaid()) {
                    $prepaymentOutstanding = true;
                } else {
                    $date = $payment->getField('DATE_PAID');
                    if ($date instanceof DateTime || $date instanceof \DateTimeInterface) {
                        $prepaymentPaidAt = max($prepaymentPaidAt, $date->getTimestamp());
                    }
                }
            }
        }
        // Legacy orders may lack per-payment timestamps; use Bitrix's paid date.
        // COD payment timestamps never postpone a production notification.
        $orderPaidDate = $row['DATE_PAYED'] ?? null;
        if ($requiresPrepayment && !$prepaymentPaidAt && $row['PAYED'] === 'Y'
            && ($orderPaidDate instanceof DateTime || $orderPaidDate instanceof \DateTimeInterface)) {
            $prepaymentPaidAt = $orderPaidDate->getTimestamp();
        }
        return ['id'=>$id, 'number'=>(string)($row['ACCOUNT_NUMBER'] ?: $id), 'site'=>$row['LID'],
            'status'=>$row['STATUS_ID'], 'cancelled'=>$row['CANCELED'] === 'Y', 'paid'=>$row['PAYED'] === 'Y',
            'payment_ready'=>$row['PAYED'] === 'Y' || ($hasPayment && !$prepaymentOutstanding),
            'prepayment_paid_ts'=>$prepaymentPaidAt,
            'created_ts'=>$row['DATE_INSERT']->getTimestamp(), 'custom_net'=>$net,
            'email'=>$email ? trim((string)$email->getValue()) : ''];
    }

    private static function allowed(): bool
    {
        $config = self::config();
        if (empty($config['enabled']) || !self::$dispatch) { return false; }
        $state = self::snapshot(self::$dispatch['id']);
        $log = ProductionUpdateLogTable::getById(self::$dispatch['id'])->fetch();
        return $state && ProductionUpdatePolicy::eligible($state, $config, static::now())
            && $state['number'] === self::$dispatch['number']
            && strcasecmp($state['email'], self::$dispatch['email']) === 0
            && ($log['RESULT'] ?? '') === 'processing';
    }

    public static function guardEvent(&$fields, &$template): ?bool
    {
        if (($template['EVENT_NAME'] ?? '') !== CustomerNotificationTemplates::PRODUCTION_EVENT) { return null; }
        if ((int)($template['ID'] ?? 0) !== (int)(self::config()['template_id'] ?? 0) || !self::allowed()
            || ($fields['EMAIL'] ?? '') !== self::$dispatch['email']
            || ($fields['ORDER_NUMBER'] ?? '') !== self::$dispatch['number']) {
            self::$blocked = true;
            return false;
        }
        return null;
    }

    public static function guardTransport(\Bitrix\Main\Event $event): ?\Bitrix\Main\EventResult
    {
        $params = $event->getParameter(0);
        if (($params['HEADER']['X-Luxsol-Notification'] ?? '') !== 'production-update') { return null; }
        if (!self::allowed()) {
            self::$blocked = true;
            return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR);
        }
        return null;
    }

    public static function run(bool $send = false): array
    {
        $config = self::config();
        if (empty($config['template_id'])) { throw new \RuntimeException('Install customer notifications first.'); }
        if ($send && empty($config['enabled'])) { throw new \RuntimeException('Production updates are disabled.'); }
        $connection = Application::getConnection();
        if (!$connection->lock('luxsol-production-updates', 0)) { return ['locked'=>true]; }
        $result = ['checked_at'=>static::now()->format(DATE_ATOM), 'enabled'=>!empty($config['enabled']),
            'preview'=>!$send, 'eligible'=>[], 'accepted'=>[], 'failed'=>[], 'blocked'=>[], 'already_processed'=>[]];
        try {
            $statuses = $config['pending_statuses'] ?? [];
            if (!$statuses) { return $result; }
            $rows = OrderTable::getList(['filter'=>['=STATUS_ID'=>$statuses, '=CANCELED'=>'N', '=LID'=>'s1',
                '>=DATE_INSERT'=>DateTime::createFromTimestamp((int)$config['active_since'])], 'select'=>['ID']]);
            while ($row = $rows->fetch()) {
                $state = self::snapshot((int)$row['ID']);
                if (!$state || !ProductionUpdatePolicy::eligible($state, $config, static::now())) { continue; }
                $id = $state['id'];
                if (ProductionUpdateLogTable::getById($id)->fetch()) { $result['already_processed'][] = $id; continue; }
                $result['eligible'][] = $id;
                if (!$send) { continue; }
                $added = ProductionUpdateLogTable::add(['ORDER_ID'=>$id, 'RESULT'=>'processing', 'CREATED_AT'=>new DateTime()]);
                if (!$added->isSuccess()) { throw new \RuntimeException('Cannot reserve production update.'); }
                self::$dispatch = $state;
                self::$blocked = false;
                try {
                    $status = MailEvent::sendImmediate(['EVENT_NAME'=>CustomerNotificationTemplates::PRODUCTION_EVENT,
                        'LID'=>$state['site'], 'MESSAGE_ID'=>(int)$config['template_id'], 'DUPLICATE'=>'N',
                        'C_FIELDS'=>['EMAIL'=>$state['email'], 'ORDER_NUMBER'=>$state['number']]]);
                    $outcome = self::$blocked ? 'blocked' : ($status === MailEvent::SEND_RESULT_SUCCESS ? 'accepted' : 'failed');
                } catch (\Throwable $error) { $outcome = 'failed'; }
                finally { self::$dispatch = []; }
                if (!ProductionUpdateLogTable::update($id, ['RESULT'=>$outcome])->isSuccess()) {
                    throw new \RuntimeException('Cannot record production update outcome; reservation retained.');
                }
                $result[$outcome][] = $id;
            }
        } finally { self::$dispatch = []; $connection->unlock('luxsol-production-updates'); }
        return $result;
    }
}

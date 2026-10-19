<?php
/** CLI-only confirmation copy and notification preparation checks. No mail is sent. */
namespace Bitrix\Main {
    class Loader { public static function includeModule($name) { return true; } }
}
namespace Bitrix\Sale {
    class Order {
        public function getId() { return 2827; }
        public function getField($name) { return $name === 'ACCOUNT_NUMBER' ? 'ORDER-2827' : null; }
        public function getSiteId() { return 's1'; }
        public function getDateInsert() { return '2026-10-09 10:26:31'; }
    }
}
namespace Bitrix\Main\Mail\Internal {
    class EventTable {
        public static array $rows = [];
        public static bool $fail = false;
        public static function getList($query) {
            if (self::$fail) { throw new \RuntimeException('Mail queue unavailable'); }
            $rows = array_values(array_filter(self::$rows, static fn($row) =>
                $row['SUCCESS_EXEC'] === $query['filter']['=SUCCESS_EXEC']
                && $row['EVENT_NAME'] === $query['filter']['=EVENT_NAME']
                && $row['LID'] === $query['filter']['=LID']));
            return new class($rows) {
                public function __construct(private array $rows) {}
                public function fetch() { return array_shift($this->rows); }
            };
        }
    }
}
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
    $root = dirname(__DIR__, 2);
    require $root . '/www/local/php_interface/room/classes/Tools/OrderConfirmation.php';
    require $root . '/www/local/php_interface/room/classes/Events/MailHandlers.php';
    use Bitrix\Main\Mail\Internal\EventTable;
    use Room\Tools\OrderConfirmation;
    use Room\Events\MailHandlers;
    $passed = 0;
    $check = static function ($name, $ok) use (&$passed) {
        if (!$ok) { throw new \RuntimeException('FAIL: ' . $name); }
        $passed++;
        echo 'PASS: ', $name, PHP_EOL;
    };
    $order = new \Bitrix\Sale\Order();
    $row = ['EVENT_NAME'=>'SALE_NEW_ORDER','LID'=>'s1','SUCCESS_EXEC'=>'Y',
        'C_FIELDS'=>['ORDER_REAL_ID'=>'2827','ORDER_ID'=>'ORDER-2827','EMAIL'=>'buyer@example.test']];
    $check('missing email event uses future tense', !OrderConfirmation::orderEmailWasSent($order, 'buyer@example.test'));
    foreach (['N','F','P'] as $status) {
        EventTable::$rows = [array_replace($row, ['SUCCESS_EXEC'=>$status])];
        $check('pending, failed or partial delivery is not claimed as sent: '.$status, !OrderConfirmation::orderEmailWasSent($order, 'buyer@example.test'));
    }
    EventTable::$rows = [$row];
    $check('matching successful order event is sent', OrderConfirmation::orderEmailWasSent($order, 'buyer@example.test'));
    $check('another recipient is not sufficient', !OrderConfirmation::orderEmailWasSent($order, 'other@example.test'));
    EventTable::$rows[0]['C_FIELDS']['ORDER_REAL_ID'] = '123';
    EventTable::$rows[0]['C_FIELDS']['ORDER_ID'] = 'OTHER-123';
    $check('another order is not sufficient', !OrderConfirmation::orderEmailWasSent($order, 'buyer@example.test'));
    EventTable::$fail = true;
    $check('mail queue error uses future tense', !OrderConfirmation::orderEmailWasSent($order, 'buyer@example.test'));
    $fields = ['EMAIL'=>'buyer@example.test'];
    $template = ['EVENT_NAME'=>'SALE_ORDER_PAID','EMAIL_FROM'=>'#SALE_EMAIL#','BODY_TYPE'=>'html','MESSAGE'=>'<html><body><p>Payment confirmed</p></body></html>'];
    MailHandlers::preparePaymentNotification($fields, $template);
    $check('payment mail uses working sender and reply address', $template['EMAIL_FROM']==='info@luxsol.sk' && $template['REPLY_TO']==='office@luxsol.sk');
    $check('merchant address is inside email body', strpos($template['MESSAGE'], 'Račianska 1571/66, 831 02 Bratislava') < strpos($template['MESSAGE'], '</body>'));
    MailHandlers::preparePaymentNotification($fields, $template);
    $check('address footer is not duplicated', substr_count($template['MESSAGE'], 'Račianska 1571/66') === 1);
    $other = ['EVENT_NAME'=>'SALE_NEW_ORDER','EMAIL_FROM'=>'info@luxsol.sk','MESSAGE'=>'Original'];
    $before = $other;
    MailHandlers::preparePaymentNotification($fields, $other);
    $check('other templates are preserved', $other === $before);
    $withdrawal = ['EVENT_NAME'=>'ODSTUPENIE_OD_ZMLUVY','MESSAGE'=>'Račianska 66, 831 02 Bratislava'];
    MailHandlers::preparePaymentNotification($fields, $withdrawal);
    $check('existing database withdrawal template receives full address', $withdrawal['MESSAGE']==='Račianska 1571/66, 831 02 Bratislava');
    echo $passed, " checks passed; no real email or database writes.\n";
}

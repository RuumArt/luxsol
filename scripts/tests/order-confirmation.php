<?php
/** CLI-only confirmation copy and notification preparation checks. No mail is sent. */
namespace Bitrix\Main {
    class Loader { public static function includeModule($name) { return true; } }
    class Event {
        public function __construct(private array $params) {}
        public function getParameter($key) { return $this->params[$key] ?? null; }
    }
    class EventResult {
        public const SUCCESS = 1;
        public function __construct(public $type, private array $params) {}
        public function getParameters() { return $this->params; }
    }
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
    require $root . '/www/local/php_interface/room/classes/Tools/Format.php';
    require $root . '/www/local/php_interface/room/classes/Tools/AdvanceInvoiceReminderTemplate.php';
    use Bitrix\Main\Mail\Internal\EventTable;
    use Room\Tools\OrderConfirmation;
    use Room\Events\MailHandlers;
    use Room\Tools\Format;
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
    $check('phone gets international prefix and readable groups', Format::phone('421908201087') === '+421 908 201 087');
    $check('already formatted phone remains stable', Format::phone('+421 908 201 087') === '+421 908 201 087');
    $check('international 00 prefix is supported', Format::phone('00421908201087') === '+421 908 201 087');
    $check('other country number is preserved', Format::phone('+49 30 123456') === '+49 30 123456');
    $check('empty phone remains empty', Format::phone('') === '');
    $check('address uses street, postcode and city without line breaks', Format::address("Trnavského\r\n 18", '84101', " Bratislava\n") === 'Trnavského 18, 841 01 Bratislava');
    $check('missing postcode does not leave extra punctuation', Format::address('Trnavského 18', '', 'Bratislava') === 'Trnavského 18, Bratislava');
    $newTemplate = ['EVENT_NAME'=>'SALE_NEW_ORDER','EMAIL_TO'=>'#EMAIL#','MESSAGE'=>'#EMAIL_PRODUCTS_TABLE#'];
    $newFields = [];
    MailHandlers::prepareNewOrderNotification($newFields, $newTemplate);
    $check('customer template is marked without modifying compiled placeholders', ($newFields['=X-Luxsol-Notification'] ?? '') === 'new-order' && $newTemplate['MESSAGE'] === '#EMAIL_PRODUCTS_TABLE#');
    $subjectTemplate = $newTemplate + ['SUBJECT'=>'#SITE_NAME#: Nová objednávka č. #ORDER_ID#'];
    MailHandlers::prepareNewOrderNotification($newFields, $subjectTemplate);
    $check('customer subject confirms the order and retains placeholders', $subjectTemplate['SUBJECT'] === '#SITE_NAME#: Potvrdenie objednávky č. #ORDER_ID#');
    $contactMail = ['HEADER'=>['X-Luxsol-Notification'=>'new-order'], 'BODY'=>'<html><head><title>Nová objednávka</title></head><body><p>Ak máte otázky, kontaktujte nás:</p><a href="tel:+421944627014">+421 944 627 014</a><a href="mailto:office@luxsol.sk">office@luxsol.sk</a></body></html>'];
    $contactBody = MailHandlers::prepareCustomerMail(new \Bitrix\Main\Event([$contactMail]))->getParameters()['BODY'];
    $check('customer theme uses confirmation title and new contact introduction', str_contains($contactBody, '<title>Potvrdenie objednávky</title>') && str_contains($contactBody, 'Ak máte akékoľvek otázky, neváhajte nás kontaktovať:'));
    $check('contact details remain after their introduction', strpos($contactBody, 'neváhajte nás kontaktovať:') < strpos($contactBody, 'tel:+421944627014') && str_contains($contactBody, 'mailto:office@luxsol.sk'));
    $contactMail['BODY'] = $contactBody;
    $check('contact wording is idempotent', MailHandlers::prepareCustomerMail(new \Bitrix\Main\Event([$contactMail]))->getParameters()['BODY'] === $contactBody);
    $mailParams = ['MESSAGE_ID'=>'13909.30','HEADER'=>['From'=>'info@luxsol.sk','X-Luxsol-Notification'=>'new-order'], 'BODY'=>'<html><body><p>Products</p><p>© 2026 Názov obchodu. Všetky práva vyhradené.</p></body></html>'];
    $prepared = MailHandlers::prepareCustomerMail(new \Bitrix\Main\Event([$mailParams]))->getParameters();
    $check('compiled theme footer gets real merchant and current year', $prepared['BODY'] === '<html><body><p>Products</p><p>© '.date('Y').' Luxsol s.r.o. Všetky práva vyhradené.</p></body></html>');
    $check('visible MID is disabled but retained in headers', $prepared['MESSAGE_ID'] === null && $prepared['HEADER']['X-MID'] === '13909.30');
    $check('internal notification marker does not leave the site', !isset($prepared['HEADER']['X-Luxsol-Notification']));
    $check('normal mail headers are preserved', $prepared['HEADER']['From'] === 'info@luxsol.sk');
    $check('unrelated mail keeps its normal identifiers', MailHandlers::prepareCustomerMail(new \Bitrix\Main\Event([['MESSAGE_ID'=>'other','HEADER'=>[]]])) === null);
    $adminTemplate = ['EVENT_NAME'=>'SALE_NEW_ORDER','EMAIL_TO'=>'admin@example.test','MESSAGE'=>'Admin'];
    $adminFields = [];
    MailHandlers::prepareNewOrderNotification($adminFields, $adminTemplate);
    $check('admin-only notification is not marked as customer mail', empty($adminFields));
    $reminderTemplate = \Room\Tools\AdvanceInvoiceReminderTemplate::fields([
        'SITE_TEMPLATE_ID'=>'MM', 'BODY_TYPE'=>'html', 'EMAIL_FROM'=>'info@luxsol.sk', 'LANGUAGE_ID'=>'ru',
    ], 's1');
    $check('reminder reuses existing customer theme, sender and site', $reminderTemplate['SITE_TEMPLATE_ID'] === 'MM' && $reminderTemplate['EMAIL_FROM'] === 'info@luxsol.sk' && $reminderTemplate['LID'] === ['s1']);
    $check('reminder has an independent event and no blind copies', $reminderTemplate['EVENT_NAME'] === 'LUXSOL_ADVANCE_INVOICE_REMINDER' && $reminderTemplate['EMAIL_TO'] === '#EMAIL#' && $reminderTemplate['BCC'] === '' && $reminderTemplate['CC'] === '');
    $check('reminder escapes the order number', str_contains($reminderTemplate['MESSAGE'], '#HTML_ORDER_NUMBER#') && !str_contains($reminderTemplate['MESSAGE'], '#ORDER_NUMBER#'));
    $check('reminder needs no unavailable invoice data', !preg_match('/#(?:HTML_)?(?:INVOICE_NUMBER|AMOUNT_WITH_CURRENCY|DUE_DATE|IBAN|VARIABLE_SYMBOL)#/', $reminderTemplate['MESSAGE'] . $reminderTemplate['SUBJECT']));
    $reminderFields = [];
    MailHandlers::prepareNewOrderNotification($reminderFields, $reminderTemplate);
    $check('reminder receives customer mail preparation', ($reminderFields['=X-Luxsol-Notification'] ?? '') === 'advance-invoice-reminder');
    $reminderParams = ['MESSAGE_ID'=>'test.31','HEADER'=>['X-Luxsol-Notification'=>'advance-invoice-reminder'], 'BODY'=>'<html><head><title>Nová objednávka</title></head><body>© 2026 Názov obchodu.</body></html>'];
    $preparedReminder = MailHandlers::prepareCustomerMail(new \Bitrix\Main\Event([$reminderParams]))->getParameters();
    $check('reminder has correct title and merchant without visible MID', $preparedReminder['MESSAGE_ID'] === null && str_contains($preparedReminder['BODY'], '<title>Pripomenutie platby</title>') && str_contains($preparedReminder['BODY'], 'Luxsol s.r.o.'));
    echo $passed, " checks passed; no real email or database writes.\n";
}

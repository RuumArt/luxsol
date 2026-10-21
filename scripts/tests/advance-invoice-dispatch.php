<?php
/** Isolated scheduler/transport regression tests; never connects to a database or SMTP. */
namespace Bitrix\Main {
    class Event { public function __construct(private array $params) {} public function getParameter($i) { return $this->params[$i]; } }
    class EventResult { const ERROR=0; public function __construct(public $type) {} }
    class Application {
        public static bool $busy=false;
        public static function getConnection() { return new class {
            public function lock($name,$timeout) { return !Application::$busy; }
            public function unlock($name) {}
        }; }
    }
}
namespace Bitrix\Main\Config {
    class Option { public static array $config=['enabled'=>true,'template_id'=>59,'pay_system_id'=>5]; public static function get(...$args) { return json_encode(self::$config); } }
}
namespace Bitrix\Main\Type {
    class DateTime extends \DateTime { public static function createFromTimestamp($ts) { return (new self())->setTimestamp($ts); } }
}
namespace Bitrix\Sale\Internals {
    class OrderTable {
        public static array $rows=[];
        public static function result(array $rows) { return new class($rows) {
            public function __construct(private array $rows) {} public function fetch() { return array_shift($this->rows); }
        }; }
        public static function getList($params) { return self::result(array_map(static fn($row)=>['ID'=>$row['ID']],array_values(self::$rows))); }
        public static function getById($id) { return self::result(isset(self::$rows[$id])?[self::$rows[$id]]:[]); }
    }
}
namespace Bitrix\Sale {
    class Order {
        public static function load($id) { return new self(\Bitrix\Sale\Internals\OrderTable::$rows[$id]); }
        public function __construct(private array $row) {}
        public function getPaymentCollection() { return [new class($this->row) {
            public function __construct(private array $row) {}
            public function getSum() { return 10; }
            public function getPaymentSystemId() { return $this->row['SYSTEM']; }
            public function isInner() { return false; }
            public function isPaid() { return $this->row['PAYMENT_PAID']??false; }
        }]; }
        public function getPropertyCollection() { return new class($this->row) {
            public function __construct(private array $row) {}
            public function getUserEmail() { return new class($this->row) {
                public function __construct(private array $row) {} public function getValue() { return $this->row['EMAIL']; }
            }; }
        }; }
    }
}
namespace Room\Tools {
    class AdvanceInvoiceReminderLogTable {
        public static array $rows=[];
        public static function getById($id) { return \Bitrix\Sale\Internals\OrderTable::result(isset(self::$rows[$id])?[self::$rows[$id]]:[]); }
        public static function result() { return new class { public function isSuccess() { return true; } }; }
        public static function add($row) { self::$rows[$row['ID']]=$row; return self::result(); }
        public static function update($id,$fields) { self::$rows[$id]=array_replace(self::$rows[$id],$fields); return self::result(); }
    }
}
namespace Bitrix\Main\Mail {
    class Event {
        const SEND_RESULT_SUCCESS='Y';
        public static array $delivered=[];
        public static $beforeCompile=null;
        public static $beforeTransport=null;
        public static bool $fail=false;
        public static function sendImmediate($params) {
            if (self::$beforeCompile) { (self::$beforeCompile)(); }
            $template=['ID'=>59,'EVENT_NAME'=>\Room\Tools\AdvanceInvoiceReminderTemplate::EVENT_NAME];
            $fields=$params['C_FIELDS'];
            if (\TestReminders::guardEvent($fields,$template)===false) { return 'N'; }
            if (self::$beforeTransport) { (self::$beforeTransport)(); }
            $result=\TestReminders::guardTransport(new \Bitrix\Main\Event([['HEADER'=>['X-Luxsol-Notification'=>'advance-invoice-reminder']]]));
            if ($result && $result->type===\Bitrix\Main\EventResult::ERROR) { return 'F'; }
            if (self::$fail) { return 'F'; }
            self::$delivered[]=$params;
            return 'Y';
        }
    }
}
namespace {
    if (PHP_SAPI!=='cli') { http_response_code(403); exit('CLI only'); }
    $root=dirname(__DIR__,2).'/www/local/php_interface/room/classes/Tools/';
    foreach(['AdvanceInvoiceReminderTemplate','AdvanceInvoiceReminderPolicy','AdvanceInvoiceReminders'] as $class) require $root.$class.'.php';
    class TestReminders extends \Room\Tools\AdvanceInvoiceReminders {
        public static \DateTimeImmutable $clock;
        protected static function now(): \DateTimeImmutable { return self::$clock; }
    }
    use Bitrix\Sale\Internals\OrderTable;
    use Bitrix\Main\Mail\Event;
    use Room\Tools\AdvanceInvoiceReminderLogTable as Log;
    $passed=0;
    $check=static function($name,$ok)use(&$passed){if(!$ok)throw new \RuntimeException('FAIL: '.$name);$passed++;echo 'PASS: ',$name,PHP_EOL;};
    $reset=static function(){
        TestReminders::$clock=new \DateTimeImmutable('2026-10-24 10:15:00 Europe/Bratislava');
        OrderTable::$rows=[101=>['ID'=>101,'ACCOUNT_NUMBER'=>'ORDER-101','DATE_STATUS'=>new \DateTimeImmutable('2026-10-21 11:00:00 Europe/Bratislava'),'LID'=>'s1','STATUS_ID'=>'OZ','PAYED'=>'N','CANCELED'=>'N','SYSTEM'=>5,'EMAIL'=>'buyer@example.test']];
        Log::$rows=[];Event::$delivered=[];Event::$beforeCompile=Event::$beforeTransport=null;Event::$fail=false;
        \Bitrix\Main\Config\Option::$config['enabled']=true;
    };
    $reset();
    $preview=TestReminders::run();
    $check('preview neither sends nor reserves a reminder',count($preview['eligible'])===1 && !Log::$rows && !Event::$delivered);
    $sent=TestReminders::run(true);
    $check('day 3 uses just the existing order number and recipient',count(Event::$delivered)===1 && Event::$delivered[0]['C_FIELDS']===['EMAIL'=>'buyer@example.test','ORDER_NUMBER'=>'ORDER-101']);
    $check('mail accepted by transport is recorded', $sent['accepted']===[101] && array_values(Log::$rows)[0]['RESULT']==='accepted');
    TestReminders::run(true);
    $check('repeated scheduler run cannot duplicate day 3',count(Event::$delivered)===1);
    TestReminders::$clock=TestReminders::$clock->modify('+2 days');TestReminders::run(true);
    $check('day 5 is delivered separately despite day 3 reservation',count(Event::$delivered)===2);
    TestReminders::$clock=TestReminders::$clock->modify('+2 days');TestReminders::run(true);
    $check('day 7 neither sends nor changes the order',count(Event::$delivered)===2 && OrderTable::$rows[101]['STATUS_ID']==='OZ');
    foreach(['PAYED'=>'Y','CANCELED'=>'Y','STATUS_ID'=>'N','SYSTEM'=>7,'PAYMENT_PAID'=>true,'EMAIL'=>''] as $field=>$value){
        $reset();OrderTable::$rows[101][$field]=$value;TestReminders::run(true);
        $check('paid/cancelled/other payment/missing recipient excluded: '.$field,!Event::$delivered && !Log::$rows);
    }
    $reset();Event::$beforeCompile=static function(){OrderTable::$rows[101]['PAYED']='Y';};
    $race=TestReminders::run(true);
    $check('payment after reservation blocks compilation',!Event::$delivered && $race['blocked']===[101]);
    $reset();Event::$beforeTransport=static function(){OrderTable::$rows[101]['PAYED']='Y';};
    $race=TestReminders::run(true);
    $check('payment after compilation blocks actual transport',!Event::$delivered && $race['blocked']===[101]);
    $reset();Event::$beforeTransport=static function(){OrderTable::$rows[101]['EMAIL']='different@example.test';};TestReminders::run(true);
    $check('recipient edits during dispatch stop stale mail',!Event::$delivered);
    $reset();Event::$fail=true;TestReminders::run(true);TestReminders::run(true);
    $check('transport failure retains reservation without repeated mail',!Event::$delivered && count(Log::$rows)===1 && array_values(Log::$rows)[0]['RESULT']==='failed');
    $reset();\Bitrix\Main\Config\Option::$config['enabled']=false;
    try{TestReminders::run(true);$check('disabled switch blocks sending',false);}catch(\RuntimeException $error){$check('disabled switch blocks sending',!Event::$delivered);}
    $reset();\Bitrix\Main\Application::$busy=true;
    $check('concurrent worker cannot dispatch',TestReminders::run(true)===['locked'=>true] && !Event::$delivered);
    echo $passed," dispatch checks passed; fake storage and transport only.\n";
}

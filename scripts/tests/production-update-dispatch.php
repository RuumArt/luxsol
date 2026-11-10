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
    class Option { public static array $config=['enabled'=>true,'template_id'=>60,'active_since'=>0,'pending_statuses'=>['N','OZ'],'cod_pay_system_ids'=>[6]]; public static function get(...$args) { return json_encode(self::$config); } }
}
namespace Bitrix\Main\Type {
    // Real Bitrix DateTime wraps PHP's DateTime; it does not implement DateTimeInterface.
    class DateTime {
        public function __construct(private ?int $timestamp=null) { $this->timestamp ??= time(); }
        public static function createFromTimestamp($ts) { return new self($ts); }
        public function getTimestamp() { return $this->timestamp; }
    }
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
        public function getBasket() { return [new class($this->row) {
            public function __construct(private array $row) {}
            public function getPropertyCollection() { return new class($this->row) {
                public function __construct(private array $row) {}
                public function getPropertyValues() { return ['SIZES_STR'=>['VALUE'=>$this->row['SIZES']??'2x3mx1 ks.']]; }
            }; }
        }]; }
        public function getPaymentCollection() {
            return array_map(static fn($data)=>new class($data) {
                public function __construct(private array $data) {}
                public function getSum() { return $this->data['sum'] ?? 10; }
                public function getPaymentSystemId() { return $this->data['system']; }
                public function isPaid() { return $this->data['paid'] ?? false; }
                public function isInner() { return $this->data['inner'] ?? false; }
                public function getField($name) { return $name === 'DATE_PAID' ? ($this->data['date_paid'] ?? null) : null; }
            }, $this->row['PAYMENTS'] ?? [['system'=>5,'paid'=>$this->row['PAYED']==='Y']]);
        }
        public function getPropertyCollection() { return new class($this->row) {
            public function __construct(private array $row) {}
            public function getUserEmail() { return new class($this->row) {
                public function __construct(private array $row) {} public function getValue() { return $this->row['EMAIL']; }
            }; }
        }; }
    }
}
namespace Room\Tools {
    class ProductionUpdateLogTable {
        public static array $rows=[];
        public static function getById($id) { return \Bitrix\Sale\Internals\OrderTable::result(isset(self::$rows[$id])?[self::$rows[$id]]:[]); }
        public static function result() { return new class { public function isSuccess() { return true; } }; }
        public static function add($row) { self::$rows[$row['ORDER_ID']]=$row; return self::result(); }
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
            $template=['ID'=>60,'EVENT_NAME'=>\Room\Tools\CustomerNotificationTemplates::PRODUCTION_EVENT];
            $fields=$params['C_FIELDS'];
            if (\TestProduction::guardEvent($fields,$template)===false) { return 'N'; }
            if (self::$beforeTransport) { (self::$beforeTransport)(); }
            $result=\TestProduction::guardTransport(new \Bitrix\Main\Event([['HEADER'=>['X-Luxsol-Notification'=>'production-update']]]));
            if ($result && $result->type===\Bitrix\Main\EventResult::ERROR) { return 'F'; }
            if (self::$fail) { return 'F'; }
            self::$delivered[]=$params;
            return 'Y';
        }
    }
}
namespace {
    $root=dirname(__DIR__,2).'/www/local/php_interface/room/classes/Tools/';
    foreach(['AdvanceInvoiceReminderTemplate','CustomerNotificationTemplates','Format','ProductionUpdatePolicy','ProductionUpdates'] as $class) require $root.$class.'.php';
    class TestProduction extends \Room\Tools\ProductionUpdates {
        public static \DateTimeImmutable $clock;
        protected static function now(): \DateTimeImmutable { return self::$clock; }
    }
    use Bitrix\Sale\Internals\OrderTable;
    use Bitrix\Main\Mail\Event;
    use Room\Tools\ProductionUpdateLogTable as Log;
    $passed=0;
    $check=static function($name,$ok)use(&$passed){if(!$ok)throw new \RuntimeException('FAIL: '.$name);$passed++;echo 'PASS: ',$name,PHP_EOL;};
    $reset=static function(){
        TestProduction::$clock=new \DateTimeImmutable('2026-11-12 10:00:00 Europe/Bratislava');
        OrderTable::$rows=[101=>['ID'=>101,'ACCOUNT_NUMBER'=>'ORDER-101','DATE_INSERT'=>new \DateTimeImmutable('2026-11-09 21:00:00 Europe/Bratislava'),'LID'=>'s1','STATUS_ID'=>'N','PAYED'=>'Y','CANCELED'=>'N','EMAIL'=>'buyer@example.test']];
        Log::$rows=[];Event::$delivered=[];Event::$beforeCompile=Event::$beforeTransport=null;Event::$fail=false;
        \Bitrix\Main\Config\Option::$config['enabled']=true;
    };
    $reset(); $preview=TestProduction::run();
    $check('preview does not send or reserve', $preview['eligible']===[101] && !Log::$rows && !Event::$delivered);
    $result=TestProduction::run(true);
    $check('dispatch uses customer email and public order number', Event::$delivered[0]['C_FIELDS']===['EMAIL'=>'buyer@example.test','ORDER_NUMBER'=>'ORDER-101']);
    $check('accepted transport is recorded', $result['accepted']===[101] && Log::$rows[101]['RESULT']==='accepted');
    TestProduction::run(true);
    $check('repeat run cannot duplicate order notification', count(Event::$delivered)===1);
    $reset();OrderTable::$rows[101]['STATUS_ID']='OZ';TestProduction::run(true);
    $check('paid OZ is eligible without moving it back to N',count(Event::$delivered)===1);
    $reset();
    $lateAt = new \DateTimeImmutable('2026-11-13 18:00 Europe/Bratislava');
    OrderTable::$rows[101]['STATUS_ID']='OZ';
    OrderTable::$rows[101]['PAYMENTS']=[['system'=>5,'paid'=>true,
        'date_paid'=>\Bitrix\Main\Type\DateTime::createFromTimestamp($lateAt->getTimestamp())]];
    TestProduction::$clock=$lateAt;
    TestProduction::run(true);
    $check('late payment does not send an immediate email', !Event::$delivered && !Log::$rows);
    TestProduction::$clock=new \DateTimeImmutable('2026-11-16 10:00 Europe/Bratislava');
    TestProduction::run(true);TestProduction::run(true);
    $check('Friday late payment sends once on Monday at ten',count(Event::$delivered)===1);
    $reset();OrderTable::$rows[101]['DATE_PAYED']=\Bitrix\Main\Type\DateTime::createFromTimestamp($lateAt->getTimestamp());
    TestProduction::$clock=new \DateTimeImmutable('2026-11-16 10:00 Europe/Bratislava');TestProduction::run(true);
    $check('legacy payment uses the order paid date as fallback',count(Event::$delivered)===1);
    $reset();OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[['system'=>6,'paid'=>true,'date_paid'=>$lateAt]];
    OrderTable::$rows[101]['DATE_PAYED']=$lateAt;TestProduction::$clock=new \DateTimeImmutable('2026-11-16 10:00 Europe/Bratislava');TestProduction::run(true);
    $check('COD collection date cannot create an overdue production email',!Event::$delivered && !Log::$rows);
    foreach ([5,7,999] as $system) {
        $reset();OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[['system'=>$system]];
        TestProduction::run(true);
        $check('unpaid transfer, card or unknown method is blocked: '.$system,!Event::$delivered && !Log::$rows);
    }
    $reset();OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[['system'=>6]];
    TestProduction::run(true);
    $check('cash on delivery need not be marked paid',count(Event::$delivered)===1);
    $reset();OrderTable::$rows[101]['STATUS_ID']='OZ';OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[['system'=>6]];
    TestProduction::run(true);
    $check('invoice-sent status still requires payment if its method is COD',!Event::$delivered && !Log::$rows);
    $reset();OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[['system'=>6],['system'=>5]];
    TestProduction::run(true);
    $check('COD does not hide unpaid prepayment in a mixed order',!Event::$delivered && !Log::$rows);
    $reset();OrderTable::$rows[101]['PAYED']='N';OrderTable::$rows[101]['PAYMENTS']=[];TestProduction::run(true);
    $check('unpaid order with no payments is excluded',!Event::$delivered && !Log::$rows);
    foreach(['STATUS_ID'=>'RE','CANCELED'=>'Y','SIZES'=>'','EMAIL'=>''] as $field=>$value){
        $reset();OrderTable::$rows[101][$field]=$value;TestProduction::run(true);
        $check('exclude ineligible order: '.$field,!Event::$delivered && !Log::$rows);
    }
    foreach(['beforeCompile','beforeTransport'] as $phase){
        $reset();Event::${$phase}=static function(){OrderTable::$rows[101]['STATUS_ID']='LP';};
        $result=TestProduction::run(true);
        $check('fresh status stops dispatch at '.$phase,!Event::$delivered && $result['blocked']===[101]);
    }
    $reset();Event::$beforeTransport=static function(){OrderTable::$rows[101]['PAYED']='N';};TestProduction::run(true);
    $check('payment revocation before transport stops delivery',!Event::$delivered);
    $reset();Event::$beforeTransport=static function(){OrderTable::$rows[101]['EMAIL']='changed@example.test';};TestProduction::run(true);
    $check('recipient change prevents stale delivery',!Event::$delivered);
    $reset();Event::$beforeTransport=static function(){\Bitrix\Main\Config\Option::$config['enabled']=false;};TestProduction::run(true);
    $check('disable before transport blocks delivery',!Event::$delivered);
    $reset();Event::$fail=true;TestProduction::run(true);TestProduction::run(true);
    $check('failed send retains reservation and is not automatically repeated',!Event::$delivered && Log::$rows[101]['RESULT']==='failed');
    $reset();\Bitrix\Main\Application::$busy=true;
    $check('concurrent worker cannot send',TestProduction::run(true)===['locked'=>true] && !Event::$delivered);
    echo $passed," dispatch checks passed; fake storage and transport only.\n";
}

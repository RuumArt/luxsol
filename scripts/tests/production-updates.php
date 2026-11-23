<?php
/** Business-day scheduling, actual transfer dates and suppression. No DB or mail. */
require dirname(__DIR__, 2) . '/www/local/php_interface/room/classes/Tools/ProductionUpdatePolicy.php';
use Room\Tools\ProductionUpdatePolicy as Policy;
$passed = 0;
$check = static function ($name, $ok) use (&$passed) {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $name); }
    $passed++; echo 'PASS: ', $name, PHP_EOL;
};
$ts = static fn($value)=>(new DateTimeImmutable($value.' Europe/Bratislava'))->getTimestamp();
foreach ([
    ['2026-11-09 21:00', '2026-11-16 10:00'],
    ['2026-11-12 08:00', '2026-11-19 10:00'],
    ['2026-11-13 23:59', '2026-11-20 10:00'],
    ['2026-11-14 12:00', '2026-11-20 10:00'],
    ['2026-10-23 12:00', '2026-10-30 10:00'],
    ['2027-03-26 12:00', '2027-04-02 10:00'],
] as [$created, $expected]) {
    $check('fifth working day after ' . $created, Policy::dueAt(new DateTimeImmutable($created . ' Europe/Bratislava'))->format('Y-m-d H:i') === $expected);
}
$state = ['site'=>'s1', 'created_ts'=>$ts('2026-11-09 21:00'), 'status'=>'N', 'paid'=>true, 'payment_ready'=>true,
    'cancelled'=>false, 'custom_net'=>true, 'email'=>'buyer@example.test'];
$config = ['active_since'=>0, 'pending_statuses'=>['N','OZ']];
$now = new DateTimeImmutable('2026-11-16 10:00 Europe/Bratislava');
$check('unchanged initial status can receive timed mail', Policy::eligible($state, $config, $now));
$check('paid invoice may remain in OZ', Policy::eligible(array_replace($state,['status'=>'OZ']), $config, $now));
foreach (['RE','LP','OT','F','UNKNOWN'] as $status) {
    $check('suppress finished or unknown status ' . $status, !Policy::eligible(array_replace($state,['status'=>$status]), $config, $now));
}
foreach (['cancelled'=>true, 'custom_net'=>false, 'email'=>'bad', 'site'=>'s2'] as $field=>$value) {
    $check('suppress ' . $field, !Policy::eligible(array_replace($state,[$field=>$value]), $config, $now));
}
$check('misconfigured ready status cannot send', !Policy::eligible(array_replace($state,['status'=>'RE']), array_replace($config,['pending_statuses'=>['RE']]), $now));
$check('orders before activation excluded', !Policy::eligible($state,array_replace($config,['active_since'=>$state['created_ts']+1]),$now));
foreach (['2026-11-16 09:59:59','2026-11-16 11:00:00','2026-11-17 10:00:00'] as $time) {
    $check('no early or late mail at ' . $time, !Policy::eligible($state,$config,new DateTimeImmutable($time.' Europe/Bratislava')));
}
$check('Bratislava timezone is used', Policy::eligible($state,$config,new DateTimeImmutable('2026-11-16 09:00 UTC')));
foreach (['N','OZ'] as $status) {
    $check('unpaid prepayment excluded in '.$status,!Policy::eligible(array_replace($state,['status'=>$status,'payment_ready'=>false]),$config,$now));
}
foreach ([
    ['2026-11-10 00:00', '2026-11-10 12:00', '2026-11-17 10:00'],
    ['2026-11-20 00:00', '2026-11-20 12:42', '2026-11-27 10:00'],
    ['2026-11-20 00:00', '2026-11-23 17:00', '2026-11-27 10:00'],
    ['2026-11-10 00:00', '2026-11-20 12:00', '2026-11-23 10:00'],
    ['2026-11-10 00:00', '2026-11-20 09:00', '2026-11-20 10:00'],
    ['2026-11-10 00:00', '2026-11-21 12:00', '2026-11-23 10:00'],
] as [$actual,$confirmed,$expected]) {
    $bank=array_replace($state,['status'=>'OZ','bank_transfer'=>true,'bank_paid_ts'=>$ts($actual),'prepayment_paid_ts'=>$ts($confirmed)]);
    $due=Policy::scheduledAt($bank);
    $check('actual transfer '.$actual.', recorded '.$confirmed.' schedules '.$expected,$due->format('Y-m-d H:i')===$expected);
    $check('invoice eligible at its adjusted due date '.$expected,Policy::eligible($bank,$config,$due));
    $check('no transfer mail before five-day due date '.$expected,!Policy::eligible($bank,$config,$due->modify('-1 second')));
}
$missing=array_replace($state,['bank_transfer'=>true,'bank_paid_ts'=>0]);
$check('missing bank payment date cannot fall back to order creation',Policy::scheduledAt($missing)===null&&!Policy::eligible($missing,$config,$now));
$oldDate=array_replace($state,['bank_transfer'=>true,'bank_paid_ts'=>$ts('2026-11-01 00:00')]);
$check('receipt predating order cannot start production before order creation',Policy::scheduledAt($oldDate)->format('Y-m-d H:i')==='2026-11-16 10:00');
$card=array_replace($state,['bank_transfer'=>false,'prepayment_paid_ts'=>$ts('2026-11-20 12:00')]);
$check('late card payment retains next-working-morning rule',Policy::scheduledAt($card)->format('Y-m-d H:i')==='2026-11-23 10:00');
$cod=array_replace($state,['bank_transfer'=>false,'bank_paid_ts'=>$ts('2026-11-20 12:00')]);
$check('COD uses creation date even if a receipt date exists',Policy::scheduledAt($cod)->format('Y-m-d H:i')==='2026-11-16 10:00');
foreach ([
    ['2026-10-23 12:00','2026-10-30 09:00'],
    ['2027-03-26 12:00','2027-04-02 08:00'],
] as [$actual,$expectedUtc]) {
    $bank=['created_ts'=>$ts('2026-01-01 12:00'),'bank_transfer'=>true,'bank_paid_ts'=>$ts($actual)];
    $check('five-day bank window crosses DST: '.$actual,Policy::scheduledAt($bank)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i')===$expectedUtc);
}
echo $passed," checks passed; no mail or database changes.\n";

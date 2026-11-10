<?php
/** Business-day scheduling and suppression. No database or mail transport. */
require dirname(__DIR__, 2) . '/www/local/php_interface/room/classes/Tools/ProductionUpdatePolicy.php';
use Room\Tools\ProductionUpdatePolicy as Policy;
$passed = 0;
$check = static function ($name, $ok) use (&$passed) {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $name); }
    $passed++; echo 'PASS: ', $name, PHP_EOL;
};
foreach ([
    ['2026-11-09 21:00', '2026-11-12 10:00'],
    ['2026-11-12 08:00', '2026-11-17 10:00'],
    ['2026-11-13 23:59', '2026-11-18 10:00'],
    ['2026-11-14 12:00', '2026-11-18 10:00'],
    ['2026-10-23 12:00', '2026-10-28 10:00'],
    ['2027-03-26 12:00', '2027-03-31 10:00'],
] as [$created, $expected]) {
    $check('third working day after ' . $created, Policy::dueAt(new DateTimeImmutable($created . ' Europe/Bratislava'))->format('Y-m-d H:i') === $expected);
}
$created = new DateTimeImmutable('2026-11-09 21:00 Europe/Bratislava');
$state = ['site'=>'s1', 'created_ts'=>$created->getTimestamp(), 'status'=>'N', 'paid'=>true, 'payment_ready'=>true,
    'cancelled'=>false, 'custom_net'=>true, 'email'=>'buyer@example.test'];
$config = ['active_since'=>0, 'pending_statuses'=>['N','OZ']];
$now = new DateTimeImmutable('2026-11-12 10:00 Europe/Bratislava');
$check('unchanged initial status can receive timed mail', Policy::eligible($state, $config, $now));
$check('paid invoice may remain in OZ without another status change', Policy::eligible(array_replace($state,['status'=>'OZ']), $config, $now));
foreach (['RE','LP','OT','F','UNKNOWN'] as $status) {
    $check('suppress non-production or finished status ' . $status, !Policy::eligible(array_replace($state,['status'=>$status]), $config, $now));
}
foreach (['cancelled'=>true, 'custom_net'=>false, 'email'=>'bad', 'site'=>'s2'] as $field=>$value) {
    $check('suppress ' . $field, !Policy::eligible(array_replace($state,[$field=>$value]), $config, $now));
}
$check('even misconfigured ready status cannot send', !Policy::eligible(array_replace($state,['status'=>'RE']), array_replace($config,['pending_statuses'=>['RE']]), $now));
$check('orders before activation are excluded', !Policy::eligible($state, array_replace($config,['active_since'=>$created->getTimestamp()+1]), $now));
foreach (['2026-11-12 09:59:59','2026-11-12 11:00:00','2026-11-13 10:00:00'] as $time) {
    $check('no early or late mail at ' . $time, !Policy::eligible($state, $config, new DateTimeImmutable($time . ' Europe/Bratislava')));
}
$check('Bratislava timezone is used even if server clock is UTC', Policy::eligible($state, $config, new DateTimeImmutable('2026-11-12 09:00 UTC')));
foreach (['N','OZ'] as $status) {
    $check('unpaid prepayment is excluded in ' . $status, !Policy::eligible(array_replace($state,['status'=>$status,'payment_ready'=>false]), $config, $now));
}
foreach ([
    ['2026-11-09 22:00:00', '2026-11-12 10:00'],
    ['2026-11-12 09:59:59', '2026-11-12 10:00'],
    ['2026-11-12 10:00:00', '2026-11-12 10:00'],
    ['2026-11-12 10:00:01', '2026-11-13 10:00'],
    ['2026-11-13 09:00:00', '2026-11-13 10:00'],
    ['2026-11-13 10:00:00', '2026-11-16 10:00'],
    ['2026-11-13 18:00:00', '2026-11-16 10:00'],
    ['2026-11-14 12:00:00', '2026-11-16 10:00'],
    ['2026-11-15 12:00:00', '2026-11-16 10:00'],
] as [$paidAt, $expected]) {
    $lateState = array_replace($state, ['status'=>'OZ',
        'prepayment_paid_ts'=>(new DateTimeImmutable($paidAt . ' Europe/Bratislava'))->getTimestamp()]);
    $scheduled = Policy::scheduledAt($lateState);
    $check('payment at ' . $paidAt . ' schedules ' . $expected, $scheduled->format('Y-m-d H:i') === $expected);
    $check('paid invoice is eligible at its adjusted time ' . $expected, Policy::eligible($lateState, $config, $scheduled));
    $check('no send before adjusted time ' . $expected, !Policy::eligible($lateState, $config, $scheduled->modify('-1 second')));
}
foreach ([
    ['2026-10-19 12:00','2026-10-23 18:00','2026-10-26 09:00'],
    ['2027-03-22 12:00','2027-03-26 18:00','2027-03-29 08:00'],
] as [$createdAt,$paidAt,$expectedUtc]) {
    $dstState = ['created_ts'=>(new DateTimeImmutable($createdAt.' Europe/Bratislava'))->getTimestamp(),
        'prepayment_paid_ts'=>(new DateTimeImmutable($paidAt.' Europe/Bratislava'))->getTimestamp()];
    $check('late payment crosses DST correctly: ' . $paidAt,
        Policy::scheduledAt($dstState)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i') === $expectedUtc);
}
echo $passed, " checks passed; no mail or database changes.\n";

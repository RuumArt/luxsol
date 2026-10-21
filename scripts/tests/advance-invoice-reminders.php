<?php
/** Reminder date windows, payment suppression and daylight-saving boundaries. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require dirname(__DIR__, 2) . '/www/local/php_interface/room/classes/Tools/AdvanceInvoiceReminderPolicy.php';

use Room\Tools\AdvanceInvoiceReminderPolicy as Policy;

$passed = 0;
$check = static function (string $name, bool $ok) use (&$passed): void {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $name); }
    $passed++;
    echo 'PASS: ', $name, PHP_EOL;
};
$sent = new DateTimeImmutable('2026-10-21 10:00:00 Europe/Bratislava');
foreach ([0, 1, 2, 3, 4, 5, 6, 7, 8, 30] as $day) {
    $now = $sent->modify('+' . $day . ' days');
    $expected = in_array($day, [3, 5], true) ? $day : null;
    $check('only days 3 and 5 are eligible: day ' . $day, Policy::dueDay($sent, $now, false, false) === $expected);
}
foreach ([3, 5] as $day) {
    $now = $sent->modify('+' . $day . ' days');
    $check('paid invoice receives no day ' . $day . ' reminder', Policy::dueDay($sent, $now, true, false) === null);
    $check('cancelled invoice receives no day ' . $day . ' reminder', Policy::dueDay($sent, $now, false, true) === null);
    $check('day ' . $day . ' reminder is not repeated', Policy::dueDay($sent, $now, false, false, [(string)$day]) === null);
}
$check('day 5 remains eligible after day 3 was sent', Policy::dueDay($sent, $sent->modify('+5 days'), false, false, [3]) === 5);
$check('future invoice dates cannot trigger a reminder', Policy::dueDay($sent, $sent->modify('-1 day'), false, false) === null);
$check('shop timezone, not server timezone, defines the day', Policy::dueDay($sent, new DateTimeImmutable('2026-10-23 22:30:00 UTC'), false, false) === 3);
$check('day 5 crosses autumn clock change as a calendar day', Policy::dueDay($sent, new DateTimeImmutable('2026-10-26 00:00:00 Europe/Bratislava'), false, false) === 5);
$state = ['status'=>'OZ', 'status_ts'=>$sent->getTimestamp(), 'bank_transfer'=>true, 'paid'=>false, 'cancelled'=>false, 'email'=>'buyer@example.test'];
$now = $sent->modify('+3 days');
$check('unpaid bank transfer in invoice-sent status is eligible', Policy::forOrder($state, $now) === 3);
foreach (['paid'=>true, 'cancelled'=>true, 'bank_transfer'=>false, 'status'=>'N', 'email'=>'', 'status_ts'=>0] as $field=>$value) {
    $check('ineligible order is excluded: '.$field, Policy::forOrder(array_replace($state, [$field=>$value]), $now) === null);
}
$check('no overnight dispatch', Policy::forOrder($state, $now->setTime(2,0)) === null);
$check('no evening dispatch', Policy::forOrder($state, $now->setTime(18,0)) === null);
echo $passed, " checks passed; no mail, orders or invoices changed.\n";

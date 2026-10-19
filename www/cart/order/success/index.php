<?php
if (!defined('BX_COMPOSITE_SKIP')) {
    define('BX_COMPOSITE_SKIP', true);
}
header('Cache-Control: private, no-store, max-age=0');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Potvrdenie objednávky');
$order = \Room\Tools\Ecommerce::paymentReturnOrder();
$number = $order ? (string)($order->getField('ACCOUNT_NUMBER') ?: $order->getId()) : '';
$emailProperty = $order ? $order->getPropertyCollection()->getUserEmail() : null;
$email = $emailProperty ? trim((string)$emailProperty->getValue()) : '';
$mailSent = $order && \Room\Tools\OrderConfirmation::orderEmailWasSent($order, $email);
$detailsUrl = '/cart/order/?ORDER_ID=' . rawurlencode($number);
$roomPurchase = \Room\Tools\Ecommerce::paymentReturnPayload();
$roomPaymentUrl = '';
require $_SERVER['DOCUMENT_ROOT'] . '/local/templates/luxol/include/purchase.php';
?>
<main class="content return payment-confirmation">
    <div class="breadcrumb">
        <div class="container">
            <?php $APPLICATION->IncludeComponent('bitrix:breadcrumb', 'bread', []); ?>
        </div>
    </div>
    <div class="container">
        <section class="payment-confirmation__card" aria-labelledby="payment-confirmation-title">
            <?php if ($order): ?>
                <h1 id="payment-confirmation-title">Ďakujeme za vašu objednávku!</h1>
                <p>Objednávku č. <strong><?=htmlspecialcharsbx($number)?></strong> sme prijali a platba prebehla úspešne.</p>
                <p>Vašu objednávku vybavíme čo najskôr.</p>
                <p>
                    <?php if ($email !== ''): ?>
                        <?=$mailSent ? 'Potvrdenie objednávky sme vám poslali na' : 'Potvrdenie objednávky vám pošleme na'?>
                        <strong><?=htmlspecialcharsbx($email)?></strong>.
                    <?php else: ?>
                        Potvrdenie objednávky vám pošleme e-mailom.
                    <?php endif; ?>
                    O odoslaní tovaru vás budeme informovať e-mailom.
                </p>
                <p>Z vašej strany už nie sú potrebné žiadne ďalšie kroky.</p>
            <?php else: ?>
                <h1 id="payment-confirmation-title">Potvrdenie objednávky</h1>
                <p>Stav platby momentálne nevieme overiť. Skontrolujte potvrdenie zaslané e-mailom alebo nás kontaktujte na <a href="mailto:office@luxsol.sk">office@luxsol.sk</a>.</p>
            <?php endif; ?>
            <div class="payment-confirmation__actions">
                <?php if ($order): ?>
                    <a class="btn btn-default" href="<?=htmlspecialcharsbx($detailsUrl)?>">Zobraziť objednávku</a>
                <?php endif; ?>
                <a class="btn btn-success" href="/catalog/">Späť do obchodu</a>
            </div>
        </section>
    </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>

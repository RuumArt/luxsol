<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if ($roomPurchase !== null || !empty($roomPaymentUrl)):
?>
<script>
$(function () {
    var purchase = <?=json_encode($roomPurchase, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)?>;
    var paymentUrl = <?=json_encode((string)($roomPaymentUrl ?? ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)?>;
    if (purchase && typeof window.roomSendPurchase === 'function') {
        window.roomSendPurchase(purchase, paymentUrl);
        return;
    }
    if (purchase) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ecommerce: null});
        window.dataLayer.push(purchase);
    }
    if (paymentUrl) {
        window.location.href = paymentUrl;
    }
});
</script>
<?php endif; ?>

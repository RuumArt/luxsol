<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<?$APPLICATION->IncludeComponent("bitrix:sale.order.payment","",Array(
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
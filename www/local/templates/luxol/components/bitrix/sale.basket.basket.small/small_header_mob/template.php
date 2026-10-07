<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?foreach ($arResult['ITEMS'] as $arItem){
	if($arItem['CAN_BUY'] == 'N'){
		$arItem["QUANTITY"] = '0';
		$arItem["PRICE"] = '0';
	}
	$totalItems += $arItem["QUANTITY"];
	$totalPrice += $arItem["QUANTITY"] * $arItem["PRICE"];
}?>
<a href="/cart/" class="btn btn-default">
	<img src="<?=SITE_TEMPLATE_PATH?>/images/shopping-cart.png" alt="">
	<span>Košík (<i class="_count"><?=count($arResult['ITEMS'])?></i>)</span>
</a>
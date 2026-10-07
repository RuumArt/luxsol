<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arResult["READY"]=="Y" || $arResult["DELAY"]=="Y" || $arResult["NOTAVAIL"]=="Y" || $arResult["SUBSCRIBE"]=="Y")
{
	CModule::IncludeModule('iblock');
	?>
	<span class="icon-basket-white"></span>в корзине

<?}?>

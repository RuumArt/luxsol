<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Porovnanie tovarov");
?>

<main class="content">
	<div class="content-images">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-1.png" alt="" class="image-1">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-2.png" alt="" class="image-2">
	</div>
	
	<div class="breadcrumb">
		<div class="container">
			<?$APPLICATION->IncludeComponent(
				"bitrix:breadcrumb",
				"bread",
			Array()
			);?>
		</div>
	</div>


<div class="compare">
 		<div class="container">
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.compare.result", 
	"compare", 
	array(
		"AJAX_MODE" => "N",
		"NAME" => "CATALOG_COMPARE_LIST",
		"IBLOCK_TYPE" => "luxol",
		"IBLOCK_ID" => "1",
		"FIELD_CODE" => array(
		),
		"PROPERTY_CODE" => array(
			0 => "ARTICLE",
			1 => "DIAMETR",
			2 => "MATERIAL",
			3 => "RAZMER",
			4 => "COLOR",
			5 => "",
			6 => "",
			7 => "",
			8 => "",
			9 => "",
			10 => "",
			11 => "",
			12 => "",
			13 => "",
			14 => "",
			15 => "",
			16 => "",
			17 => "",
			18 => "",
			19 => "",
			20 => "",
			21 => "",
			22 => "",
			23 => "",
		),
		"OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "",
			1 => "CML2_LINK",
			2 => "ITEM",
			3 => "DISCOUNT",
			4 => "PROCENT",
			5 => "NEW_PRICE",
			6 => "",
		),
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_ORDER" => "asc",
		"DETAIL_URL" => "",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"DISPLAY_ELEMENT_SELECT_BOX" => "Y",
		"ELEMENT_SORT_FIELD_BOX" => "name",
		"ELEMENT_SORT_ORDER_BOX" => "asc",
		"ELEMENT_SORT_FIELD_BOX2" => "id",
		"ELEMENT_SORT_ORDER_BOX2" => "desc",
		"HIDE_NOT_AVAILABLE" => "N",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "Y",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "Y",
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"TEMPLATE_THEME" => "blue",
		"COMPONENT_TEMPLATE" => "compare",
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
</div>
</div>
				
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
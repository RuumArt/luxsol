<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Obľúbený");
$APPLICATION->SetPageProperty("keywords", "Obľúbený");
$APPLICATION->SetPageProperty("description", "Obľúbený");
$APPLICATION->SetTitle("Obľúbený");
?>
<style>
.catalog-right{
	width: 100%;
}
.product{
	width: calc(33% - 8px)
}
</style>
<main class="content">
	<div class="content-images">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-1.png" alt="" class="image-1">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-2.png" alt="" class="image-2">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-3.png" alt="" class="image-3">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-4.png" alt="" class="image-4">
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
	
	<section class="catalog">
		<div class="container">
			<div class="section-header">
				<h2 class="section-header__title">
				Vybrané
				</h2>
			</div>
 			
 			<form action="" class="filter" id="sort-count">
 				<div class="filter-sort">
 					<span class="filter-title">Zoradiť podľa</span>
 					<label class="filter-sort__select">
 						<select name="sort" onchange="$('#sort-count').submit();">
 							<option value="name-asc" <?if($_GET['sort'] == 'name-asc'):?>selected<?endif?>>Názov od A do Z</option>
 							<option value="name-desc" <?if($_GET['sort'] == 'name-desc'):?>selected<?endif?>>Názov od Z do A</option>
 							<option value="show_counter-asc" <?if($_GET['sort'] == 'show_counter-asc' || empty($_GET['sort'])):?>selected<?endif?>>Podľa ratingu</option>
 						</select>
 					</label>
 				</div>
 				<div class="filter-count">
 					<span class="filter-title">Produkty na stránke</span>
 					<label  class="radio" onclick="$('#sort-count').submit();">
 						<input type="radio" name="count" value="20" hidden <?if($_GET['count'] == 20):?>checked<?endif?>>
 						<span class="radio-text">20</span>
 					</label>
 					<label  class="radio" onclick="$('#sort-count').submit();">
 						<input type="radio" name="count" value="40" hidden <?if($_GET['count'] == 40):?>checked<?endif?>>
 						<span class="radio-text">40</span>
 					</label>
 					<label  class="radio" onclick="$('#sort-count').submit();">
 						<input type="radio" name="count" value="60" hidden <?if($_GET['count'] == 60):?>checked<?endif?>>
 						<span class="radio-text">60</span>
 					</label> 					 					
 				</div> 				
 			</form>
 			<div class="catalog-content">
 				<div class="catalog-right">
 					<?if (!empty($mas)){
 					$yes = $APPLICATION->get_cookie("favorits");
					$mas = json_decode($yes,true);
 					
 					if(!empty($_GET['sort'])){
 						$sort_new = explode("-", $_GET['sort']);
 						$by = $sort_new[0];
 						$sort = $sort_new[1];
 					}
 					else{
 						$by = 'show_counter';
 						$sort = 'asc';
 					}
 					
 					if(!empty($_GET['count'])){
 						$count = $_GET['count'];
 					}
 					else{
 						$count = 20;
 					}
 					
 					 global $arrFilter;
          			$arrFilter = array("ID" => $mas);
 					?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:catalog.section", 
							"catalog", 
							array(
								"COMPONENT_TEMPLATE" => "catalog",
								"IBLOCK_TYPE" => "luxol",
								"IBLOCK_ID" => "1",
								"SECTION_ID" => $ID_SECT,
								"SECTION_CODE" => "",
								"SECTION_USER_FIELDS" => array(
									0 => "",
									1 => "",
								),
								"FILTER_NAME" => "arrFilter",
								"INCLUDE_SUBSECTIONS" => "Y",
								"SHOW_ALL_WO_SECTION" => "Y",
								"CUSTOM_FILTER" => "",
								"HIDE_NOT_AVAILABLE" => "N",
								"HIDE_NOT_AVAILABLE_OFFERS" => "N",
								"ELEMENT_SORT_FIELD" => $by,
								"ELEMENT_SORT_ORDER" => $sort,
								"ELEMENT_SORT_FIELD2" => "id",
								"ELEMENT_SORT_ORDER2" => "desc",
								"PAGE_ELEMENT_COUNT" => $count,
								"LINE_ELEMENT_COUNT" => "3",
								"OFFERS_LIMIT" => "5",
								"BACKGROUND_IMAGE" => "-",
								"TEMPLATE_THEME" => "blue",
								"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false}]",
								"ENLARGE_PRODUCT" => "STRICT",
								"PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
								"SHOW_SLIDER" => "Y",
								"SLIDER_INTERVAL" => "3000",
								"SLIDER_PROGRESS" => "N",
								"ADD_PICT_PROP" => "-",
								"LABEL_PROP" => "",
								"PRODUCT_SUBSCRIPTION" => "Y",
								"SHOW_DISCOUNT_PERCENT" => "N",
								"SHOW_OLD_PRICE" => "N",
								"SHOW_MAX_QUANTITY" => "N",
								"SHOW_CLOSE_POPUP" => "N",
								"MESS_BTN_BUY" => "Купить",
								"MESS_BTN_ADD_TO_BASKET" => "В корзину",
								"MESS_BTN_SUBSCRIBE" => "Подписаться",
								"MESS_BTN_DETAIL" => "Подробнее",
								"MESS_NOT_AVAILABLE" => "Нет в наличии",
								"RCM_TYPE" => "personal",
								"RCM_PROD_ID" => $_REQUEST["PRODUCT_ID"],
								"SHOW_FROM_SECTION" => "N",
								"SECTION_URL" => "",
								"DETAIL_URL" => "",
								"SECTION_ID_VARIABLE" => "SECTION_ID",
								"SEF_MODE" => "N",
								"AJAX_MODE" => "N",
								"AJAX_OPTION_JUMP" => "N",
								"AJAX_OPTION_STYLE" => "Y",
								"AJAX_OPTION_HISTORY" => "N",
								"AJAX_OPTION_ADDITIONAL" => "",
								"CACHE_TYPE" => "A",
								"CACHE_TIME" => "36000000",
								"CACHE_GROUPS" => "Y",
								"SET_TITLE" => "Y",
								"SET_BROWSER_TITLE" => "Y",
								"BROWSER_TITLE" => "-",
								"SET_META_KEYWORDS" => "Y",
								"META_KEYWORDS" => "-",
								"SET_META_DESCRIPTION" => "Y",
								"META_DESCRIPTION" => "-",
								"SET_LAST_MODIFIED" => "N",
								"USE_MAIN_ELEMENT_SECTION" => "N",
								"ADD_SECTIONS_CHAIN" => "N",
								"CACHE_FILTER" => "N",
								"ACTION_VARIABLE" => "action",
								"PRODUCT_ID_VARIABLE" => "id",
								"PRICE_CODE" => array(
									0 => "BASE",
								),
								"USE_PRICE_COUNT" => "N",
								"SHOW_PRICE_COUNT" => "1",
								"PRICE_VAT_INCLUDE" => "Y",
								"CONVERT_CURRENCY" => "N",
								"BASKET_URL" => "/personal/basket.php",
								"USE_PRODUCT_QUANTITY" => "N",
								"PRODUCT_QUANTITY_VARIABLE" => "quantity",
								"ADD_PROPERTIES_TO_BASKET" => "Y",
								"PRODUCT_PROPS_VARIABLE" => "prop",
								"PARTIAL_PRODUCT_PROPERTIES" => "N",
								"ADD_TO_BASKET_ACTION" => "ADD",
								"DISPLAY_COMPARE" => "N",
								"USE_ENHANCED_ECOMMERCE" => "N",
								"PAGER_TEMPLATE" => ".default",
								"DISPLAY_TOP_PAGER" => "N",
								"DISPLAY_BOTTOM_PAGER" => "Y",
								"PAGER_TITLE" => "Товары",
								"PAGER_SHOW_ALWAYS" => "N",
								"PAGER_DESC_NUMBERING" => "N",
								"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
								"PAGER_SHOW_ALL" => "N",
								"PAGER_BASE_LINK_ENABLE" => "N",
								"LAZY_LOAD" => "N",
								"LOAD_ON_SCROLL" => "N",
								"SET_STATUS_404" => "N",
								"SHOW_404" => "N",
								"MESSAGE_404" => "",
								"COMPATIBLE_MODE" => "Y",
								"DISABLE_INIT_JS_IN_COMPONENT" => "N"
							),
							false
						);?>
					<?}else{?>
					<p>Žiadne položky vo vašom zozname želaní <a href="/catalog/">Pridať</a></p>
					<?}?>

				</div>
			</div>
		</div>
	</section>
<script>
$(".product__count__item").prepend('<div class="dec button">-</div>'), $(".product__count__item").append('  <div class="inc button">+</div>'), $(".button").on("click", function() {
    var e = $(this),
        t = e.parent().find("input").val();
    if (e.hasClass("inc")) var i = parseFloat(t) + 1;
    else if (1 < t) i = parseFloat(t) - 1;
    else i = 1;
    e.parent().find("input").val(i)
})
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Hľadať");
// Страницы результатов поиска в индексе не нужны: у каждого запроса
// свой набор товаров, и все они уже есть в каталоге. follow оставляем,
// чтобы поисковик проходил по ссылкам на товары
$APPLICATION->SetPageProperty("robots", "noindex, follow");?><main class="page-content">
<div class="search-block">
	<div class="container">
		<div class="section-header">
			<h1 class="section-header__title">Výsledky vyhľadávania - "<?=$_GET['q']?>"</h1>
		</div>
		 <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.search",
	"bb",
	Array(
		"ACTION_VARIABLE" => "action",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BASKET_URL" => "/personal/basket.php",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "N",
		"COMPONENT_TEMPLATE" => ".default",
		"CONVERT_CURRENCY" => "N",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_COMPARE" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_FIELD2" => "id",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_SORT_ORDER2" => "desc",
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
		"IBLOCK_ID" => "1",
		"IBLOCK_TYPE" => "luxol",
		"LINE_ELEMENT_COUNT" => "3",
		"NO_WORD_LOGIC" => "N",
		"OFFERS_CART_PROPERTIES" => "",
		"OFFERS_FIELD_CODE" => array(0=>"",1=>"",),
		"OFFERS_LIMIT" => "5",
		"OFFERS_PROPERTY_CODE" => array(0=>"",1=>"",),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_ORDER2" => "desc",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Produkty",
		"PAGE_ELEMENT_COUNT" => "30",
		"PRICE_CODE" => array(0=>"BASE",),
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_PROPERTIES" => "",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PROPERTY_CODE" => array(0=>"",1=>"",),
		"RESTART" => "N",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"SECTION_URL" => "",
		"SHOW_PRICE_COUNT" => "1",
		"USE_LANGUAGE_GUESS" => "N",
		"USE_PRICE_COUNT" => "N",
		"USE_PRODUCT_QUANTITY" => "N",
		"USE_SEARCH_RESULT_ORDER" => "N",
		"USE_TITLE_RANK" => "N"
	)
);?>
	</div>
</div>
 </main><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
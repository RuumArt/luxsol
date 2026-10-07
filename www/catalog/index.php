<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "VŠETKY KATEGÓRIE");
$APPLICATION->SetTitle("VŠETKY KATEGÓRIE");
?>
<? if(!empty($_REQUEST['CODE_PATH'])){
        $code = explode("/", $_REQUEST['CODE_PATH']);
        foreach ($code as $val){
                if(!empty($val)){
                        $codes = $val;
                }
        }
        CModule::IncludeModule("iblock");
        $arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y", "CODE" => $codes);
        $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
        while ($arSect = $rsSect->GetNext())
        {
            $ID_SECT = $arSect['ID'];
            $descr = $arSect['~DESCRIPTION'];
        }

        if(!empty($ID_SECT)){
                $ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(1,$ID_SECT);
                $IPROPERTY  = $ipropValues->getValues();
        }
}

if(!empty($ID_SECT) && !empty($_REQUEST['CODE_PATH']) || empty($_REQUEST['CODE_PATH'])):
?>
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
                                <h1 class="section-header__title">
                                <?if(!empty($IPROPERTY['SECTION_PAGE_TITLE'])):?>
                                        <?=$IPROPERTY['SECTION_PAGE_TITLE']?>
                                <?else:?>
                                        VŠETKY KATEGÓRIE
                                <?endif?>
                                </h1>
                        </div>

            <?

                        $arFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => 1, 'SECTION_ID' => $ID_SECT); // выберет потомков без учета активности
                        $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
                        while ($arSect = $rsSect->GetNext())
                        {
                                $arChild[] = $arSect;
                        }

            ?>

            <? if(!empty($arChild)): ?>
            <div class="category-list">
                <? foreach($arChild as $key => $section): ?>
                    <div class="category-list__item">
                        <div class="category-card">
                            <div class="category-card__body">
                                <div class="category-card__image">
                                    <? if(!empty($section['PICTURE'])): $file = CFile::ResizeImageGet($section['PICTURE'], array('width'=>500, 'height'=>500), BX_RESIZE_IMAGE_EXACT, true); ?>
                                        <img src="<?=$file["src"]?>" alt="<?=htmlspecialcharsbx($section['NAME'])?>">
                                    <? endif; ?>
                                </div>
                                <div class="category-card__title">
                                    <?= $section['NAME'] ?>
                                </div>
                            </div>
                            <a href="<?= $section['SECTION_PAGE_URL'] ?>" class="category-card__link"></a>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>

            <? else: ?>

                        <form action="" class="filter" id="sort-count">
                                <div class="filter-sort">
                                        <span class="filter-title">Zoradiť podľa</span>
                                        <label class="filter-sort__select">
                                                <select name="sort" onchange="$('#sort-count').submit();">
                                                        <option value="name-asc" <?if($_GET['sort'] == 'name-asc'):?>selected<?endif?>>Názov od A do Z</option>
                                                        <option value="name-desc" <?if($_GET['sort'] == 'name-desc'):?>selected<?endif?>>Názov od Z do A</option>
                                                        <option value="SCALED_PRICE_1-desc" <? if($_GET['sort'] == 'SCALED_PRICE_1-desc' || empty($_GET['sort'])):?>selected<? endif?>>Cena &#8595;</option>
                            <option value="SCALED_PRICE_1-asc" <? if($_GET['sort'] == 'SCALED_PRICE_1-asc'):?>selected<? endif?>>Cena &#8593;</option>
                                                </select>
                                        </label>
                                </div>
                                <div class="filter-count">
                                        <span class="filter-title">Produkty na stránke</span>
                                        <label  class="radio" onclick="$('#sort-count').submit();">
                                                <input type="radio" name="count" value="21" hidden <?if($_GET['count'] == 21):?>checked<?endif?>>
                                                <span class="radio-text">21</span>
                                        </label>
                                        <label  class="radio" onclick="$('#sort-count').submit();">
                                                <input type="radio" name="count" value="39" hidden <?if($_GET['count'] == 39):?>checked<?endif?>>
                                                <span class="radio-text">39</span>
                                        </label>
                                        <label  class="radio" onclick="$('#sort-count').submit();">
                                                <input type="radio" name="count" value="60" hidden <?if($_GET['count'] == 60):?>checked<?endif?>>
                                                <span class="radio-text">60</span>
                                        </label>
                                </div>
                                <div class="filter-mobile-btn">
                                        <a href="#" class="btn btn-success js-open-filter">Upresniť parametre</a>
                                </div>
                        </form>

                        <? endif; ?>

                        <div class="catalog-content" style="<?=!empty($arChild) ? 'display: none;' : '' ?>">
                                <?$APPLICATION->IncludeComponent("bitrix:catalog.smart.filter", "filter", Array(
                                        "COMPONENT_TEMPLATE" => ".default",
                                                "IBLOCK_TYPE" => "luxol",       // Тип инфоблока
                                                "IBLOCK_ID" => "1",     // Инфоблок
                                                "SECTION_ID" => $ID_SECT,       // ID раздела инфоблока
                                                "SECTION_CODE" => "",   // Код раздела
                                                "PREFILTER_NAME" => "smartPreFilter",   // Имя входящего массива для дополнительной фильтрации элементов
                                                "FILTER_NAME" => "arrFilter",   // Имя выходящего массива для фильтрации
                                                "HIDE_NOT_AVAILABLE" => "N",    // Не отображать товары, которых нет на складах
                                                "TEMPLATE_THEME" => "blue",     // Цветовая тема
                                                "FILTER_VIEW_MODE" => "vertical",       // Вид отображения
                                                "POPUP_POSITION" => "left",     // Позиция для отображения всплывающего блока с информацией о фильтрации
                                                "DISPLAY_ELEMENT_COUNT" => "Y", // Показывать количество
                                                "SEF_MODE" => "N",      // Включить поддержку ЧПУ
                                                "CACHE_TYPE" => "A",    // Тип кеширования
                                                "CACHE_TIME" => "36000000",     // Время кеширования (сек.)
                                                "CACHE_GROUPS" => "Y",  // Учитывать права доступа
                                                "SAVE_IN_SESSION" => "N",       // Сохранять установки фильтра в сессии пользователя
                                                "PAGER_PARAMS_NAME" => "arrPager",      // Имя массива с переменными для построения ссылок в постраничной навигации
                                                "PRICE_CODE" => "",     // Тип цены
                                                "CONVERT_CURRENCY" => "N",      // Показывать цены в одной валюте
                                                "XML_EXPORT" => "N",    // Включить поддержку Яндекс Островов
                                                "SECTION_TITLE" => "-", // Заголовок
                                                "SECTION_DESCRIPTION" => "-",   // Описание
                                        ),
                                        false
                                );?>

                                <div class="catalog-right">
                                        <? if(!empty($_GET['sort'])){
                                                $sort_new = explode("-", $_GET['sort']);
                                                $by = $sort_new[0];
                                                $sort = $sort_new[1];
                                        }
                                        else{
                                                $by = 'SCALED_PRICE_1';
                                                $sort = 'desc';
                                        }

                                        if(!empty($_GET['count'])){
                                                $count = $_GET['count'];
                                        }
                                        else{
                                                $count = 21;
                                        }
                                        ?>
                                                <? $APPLICATION->IncludeComponent(
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
                "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"OR\",\"True\":\"True\"},\"CHILDREN\":[]}",
                "HIDE_NOT_AVAILABLE" => "N",
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "ELEMENT_SORT_FIELD" => $by,
                "ELEMENT_SORT_ORDER" => $sort,
                "ELEMENT_SORT_FIELD2" => "SCALED_PRICE_1",
                "ELEMENT_SORT_ORDER2" => "asc",
                "PAGE_ELEMENT_COUNT" => $count,
                "LINE_ELEMENT_COUNT" => "3",
                "OFFERS_LIMIT" => "0",
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
                "CACHE_TYPE" => "N",
                "CACHE_TIME" => "36000000",
                "CACHE_GROUPS" => "N",
                "SET_TITLE" => "Y",
                "SET_BROWSER_TITLE" => "Y",
                "BROWSER_TITLE" => "-",
                "SET_META_KEYWORDS" => "Y",
                "META_KEYWORDS" => "-",
                "SET_META_DESCRIPTION" => "Y",
                "META_DESCRIPTION" => "-",
                "SET_LAST_MODIFIED" => "Y",
                "USE_MAIN_ELEMENT_SECTION" => "Y",
                "ADD_SECTIONS_CHAIN" => "Y",
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
                "PAGER_TEMPLATE" => "custom",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "Y",
                "PAGER_TITLE" => "Produkty",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N",
                "PAGER_BASE_LINK_ENABLE" => "N",
                "LAZY_LOAD" => "N",
                "LOAD_ON_SCROLL" => "N",
                "SET_STATUS_404" => "Y",
                "SHOW_404" => "Y",
                "MESSAGE_404" => "",
                "COMPATIBLE_MODE" => "Y",
                "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                "OFFERS_SORT_FIELD" => "sort",
                "OFFERS_SORT_ORDER" => "asc",
                "OFFERS_SORT_FIELD2" => "id",
                "OFFERS_SORT_ORDER2" => "desc",
                "OFFERS_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                ),
                "FILE_404" => ""
        ),
        false
);?>
                                        <?if(empty($_GET['PAGEN_1']) && empty($_GET['set_filter'])):?>
                    <div class="section-description text-block std">
                                            <?=$descr?>
                    </div>
                                        <?endif?>
                                </div>
                        </div>

                </div>
        </section>
<?else:?>
<?
$code = explode("/", $_REQUEST['CODE_PATH']);
        foreach ($code as $val){
                if(!empty($val)){
                        $codes_new[] = $val;
                }
        }
$count = count($codes_new);
$arFilter = array('IBLOCK_ID' => 1, "ACTIVE" => "Y", "CODE" => $codes_new); // выберет потомков без учета активности
   $res = CIBlockElement::GetList(array('left_margin' => 'asc'),$arFilter,false, false,array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL"));
   while ($arSect = $res->GetNext())
   {
       $URL = $arSect['DETAIL_PAGE_URL'];
   }
?>
<?
// Адреса сравниваются в раскодированном виде: текущий адрес приходит
// закодированным (%D0%B3), а DETAIL_PAGE_URL - нет. Без этого товар
// с не-латинскими буквами в коде уходил в бесконечный редирект
?>
<?if(!empty($URL) && rawurldecode($APPLICATION->GetCurPage()) === rawurldecode($URL)):?>
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


                <?$APPLICATION->IncludeComponent(
        "bitrix:catalog.element",
        "element",
        array(
                "COMPONENT_TEMPLATE" => "element",
                "IBLOCK_TYPE" => "luxol",
                "IBLOCK_ID" => "1",
                "ELEMENT_ID" => "",
                "ELEMENT_CODE" => $codes,
                "SECTION_ID" => "",
                "SECTION_CODE" => $sect,
                "SHOW_DEACTIVATED" => "N",
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "OFFERS_LIMIT" => "0",
                "BACKGROUND_IMAGE" => "-",
                "TEMPLATE_THEME" => "blue",
                "PRODUCT_INFO_BLOCK_ORDER" => "sku,props",
                "PRODUCT_PAY_BLOCK_ORDER" => "rating,price,priceRanges,quantityLimit,quantity,buttons",
                "ADD_PICT_PROP" => "-",
                "LABEL_PROP" => "",
                "DISPLAY_NAME" => "Y",
                "IMAGE_RESOLUTION" => "16by9",
                "SHOW_SLIDER" => "N",
                "DETAIL_PICTURE_MODE" => array(
                        0 => "POPUP",
                        1 => "MAGNIFIER",
                ),
                "ADD_DETAIL_TO_SLIDER" => "N",
                "DISPLAY_PREVIEW_TEXT_MODE" => "E",
                "PRODUCT_SUBSCRIPTION" => "Y",
                "SHOW_DISCOUNT_PERCENT" => "N",
                "SHOW_OLD_PRICE" => "N",
                "SHOW_MAX_QUANTITY" => "N",
                "SHOW_CLOSE_POPUP" => "N",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                "MESS_BTN_SUBSCRIBE" => "Подписаться",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "USE_VOTE_RATING" => "N",
                "USE_COMMENTS" => "N",
                "BRAND_USE" => "N",
                "MESS_PRICE_RANGES_TITLE" => "Цены",
                "MESS_DESCRIPTION_TAB" => "Описание",
                "MESS_PROPERTIES_TAB" => "Характеристики",
                "MESS_COMMENTS_TAB" => "Комментарии",
                "SECTION_URL" => "",
                "DETAIL_URL" => "",
                "SECTION_ID_VARIABLE" => "SECTION_ID",
                "CHECK_SECTION_ID_VARIABLE" => "N",
                "SEF_MODE" => "N",
                "CACHE_TYPE" => "N",
                "CACHE_TIME" => "36000000",
                "CACHE_GROUPS" => "Y",
                "SET_TITLE" => "Y",
                "SET_CANONICAL_URL" => "N",
                "SET_BROWSER_TITLE" => "Y",
                "BROWSER_TITLE" => "-",
                "SET_META_KEYWORDS" => "Y",
                "META_KEYWORDS" => "-",
                "SET_META_DESCRIPTION" => "Y",
                "META_DESCRIPTION" => "-",
                "SET_LAST_MODIFIED" => "N",
                "USE_MAIN_ELEMENT_SECTION" => "N",
                "STRICT_SECTION_CHECK" => "N",
                "ADD_SECTIONS_CHAIN" => "Y",
                "ADD_ELEMENT_CHAIN" => "Y",
                "ACTION_VARIABLE" => "action",
                "PRODUCT_ID_VARIABLE" => "id",
                "DISPLAY_COMPARE" => "N",
                "PRICE_CODE" => array(
                        0 => "BASE",
                ),
                "USE_PRICE_COUNT" => "N",
                "SHOW_PRICE_COUNT" => "1",
                "PRICE_VAT_INCLUDE" => "Y",
                "PRICE_VAT_SHOW_VALUE" => "N",
                "CONVERT_CURRENCY" => "N",
                "USE_RATIO_IN_RANGES" => "Y",
                "BASKET_URL" => "/personal/basket.php",
                "USE_PRODUCT_QUANTITY" => "N",
                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "PRODUCT_PROPS_VARIABLE" => "prop",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "ADD_TO_BASKET_ACTION" => array(
                        0 => "BUY",
                ),
                "ADD_TO_BASKET_ACTION_PRIMARY" => array(
                        0 => "BUY",
                ),
                "LINK_IBLOCK_TYPE" => "",
                "LINK_IBLOCK_ID" => "",
                "LINK_PROPERTY_SID" => "",
                "LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
                "USE_GIFTS_DETAIL" => "Y",
                "USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
                "GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "4",
                "GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
                "GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
                "GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
                "GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
                "GIFTS_SHOW_OLD_PRICE" => "Y",
                "GIFTS_SHOW_NAME" => "Y",
                "GIFTS_SHOW_IMAGE" => "Y",
                "GIFTS_MESS_BTN_BUY" => "Выбрать",
                "GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "4",
                "GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
                "GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
                "USE_ENHANCED_ECOMMERCE" => "N",
                "SET_STATUS_404" => "Y",
                "SHOW_404" => "Y",
                "MESSAGE_404" => "",
                "COMPATIBLE_MODE" => "Y",
                "USE_ELEMENT_COUNTER" => "Y",
                "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                "SET_VIEWED_IN_COMPONENT" => "Y",
                "OFFERS_FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                ),
                "OFFERS_SORT_FIELD" => "sort",
                "OFFERS_SORT_ORDER" => "asc",
                "OFFERS_SORT_FIELD2" => "id",
                "OFFERS_SORT_ORDER2" => "desc",
                "FILE_404" => "",
                "ADDITIONAL_FILTER_NAME" => "elementFilter",
                "MAIN_BLOCK_PROPERTY_CODE" => "",
                "OFFER_ADD_PICT_PROP" => "-",
                "MESS_NOT_AVAILABLE_SERVICE" => "Недоступно",
                "SHOW_SKU_DESCRIPTION" => "N"
        ),
        false
);?>

</main>
<?elseif(!empty($URL)):?>
<?
// Товар открыт по старому адресу - перенаправляем на актуальный
LocalRedirect($URL, false, "301 Moved permanently");
?>
<?else:?>
<?
// Товар не найден или выключен. Раньше здесь был LocalRedirect с пустым
// $URL, который вёл на текущий же адрес - бесконечный редирект.
// Теперь честный 404: статус, страница /404.php и завершение
\Bitrix\Iblock\Component\Tools::process404('', true, true, true);
?>
<?endif?>

<?endif?>
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

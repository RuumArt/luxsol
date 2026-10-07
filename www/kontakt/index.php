<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Kontaktujte nás");

?><main class="content">
<div class="content-images">
 <img src="/local/templates/luxol/images/img-1.png" alt="" class="image-1"> <img src="/local/templates/luxol/images/img-2.png" alt="" class="image-2"> <img src="/local/templates/luxol/images/img-3.png" alt="" class="image-3"> <img src="/local/templates/luxol/images/img-4.png" alt="" class="image-4">
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
<div class="contacts">
	<div class="container">
		<div class="section-header">
			<h1 class="section-header__title">Kontaktujte nás</h1>
		</div>
		<div class="contacts-row">
<iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d1872.3081782101133!2d17.129556055011577!3d48.17882189221119!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNDjCsDEwJzQzLjEiTiAxN8KwMDcnNDcuMyJF!5e0!3m2!1sru!2sru!4v1788710307352!5m2!1sru!2sru" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>

<br>
			 <?$APPLICATION->IncludeComponent(
	"bitrix:news.detail",
	"contacts",
	Array(
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_ELEMENT_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BROWSER_TITLE" => "-",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "N",
		"CHECK_DATES" => "Y",
		"COMPONENT_TEMPLATE" => ".default",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"ELEMENT_CODE" => "",
		"ELEMENT_ID" => "151",
		"FIELD_CODE" => array(0=>"",1=>"",),
		"IBLOCK_ID" => "4",
		"IBLOCK_TYPE" => "luxol",
		"IBLOCK_URL" => "",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"MESSAGE_404" => "",
		"META_DESCRIPTION" => "-",
		"META_KEYWORDS" => "-",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Страница",
		"PROPERTY_CODE" => array(0=>"EMAIL",1=>"ADRES",2=>"PHONE",3=>"SOC",),
		"SET_BROWSER_TITLE" => "Y",
		"SET_CANONICAL_URL" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "Y",
		"SHOW_404" => "N",
		"STRICT_SECTION_CHECK" => "N",
		"USE_PERMISSIONS" => "N",
		"USE_SHARE" => "N"
	)
);?>
			<div id="rescont" class="contacts-right" style="margin-top: 20px;">
            <div class="contact-form">
				<div class="contact-form__header">
					<div class="contact-form__title">
                        Napíšte nám
					</div>
					<div class="contact-form__desc">
                        Objednajte hovor a naši odborníci Vám zavolajú čoskoro!
					</div>
				</div>
				<div class="contact-form__body">
		            <?$APPLICATION->IncludeComponent(
		            	"luxol:iblock.element.add.form",
		            	"template1",
		            	Array(
		            		"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
		            		"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
		            		"CUSTOM_TITLE_DETAIL_PICTURE" => "",
		            		"CUSTOM_TITLE_DETAIL_TEXT" => "",
		            		"CUSTOM_TITLE_IBLOCK_SECTION" => "",
		            		"CUSTOM_TITLE_NAME" => "",
		            		"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
		            		"CUSTOM_TITLE_PREVIEW_TEXT" => "",
		            		"CUSTOM_TITLE_TAGS" => "",
		            		"DEFAULT_INPUT_SIZE" => "30",
		            		"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
		            		"ELEMENT_ASSOC" => "CREATED_BY",
		            		"GROUPS" => array("2"),
		            		"IBLOCK_ID" => "3",
		            		"IBLOCK_TYPE" => "",
		            		"LEVEL_LAST" => "Y",
		            		"LIST_URL" => "",
		            		"MAX_FILE_SIZE" => "0",
		            		"MAX_LEVELS" => "100000",
		            		"MAX_USER_ENTRIES" => "100000",
		            		"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
		            		"PROPERTY_CODES" => array("8","NAME","PREVIEW_TEXT"),
		            		"PROPERTY_CODES_REQUIRED" => array(),
		            		"RESIZE_IMAGES" => "N",
		            		"SEF_MODE" => "N",
		            		"STATUS" => "ANY",
		            		"STATUS_NEW" => "N",
		            		"USER_MESSAGE_ADD" => "",
		            		"USER_MESSAGE_EDIT" => "",
		            		"USE_CAPTCHA" => "Y"
		            	)
		            );?>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
 </main><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
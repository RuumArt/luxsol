<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>

<?
if(!empty($_POST['id']) && empty($_POST['del'])){
$APPLICATION->IncludeComponent("bitrix:catalog.element", "comp", Array(
	"IBLOCK_TYPE" => "luxol",	// Тип инфоблока
		"IBLOCK_ID" => "1",	// Инфоблок
		"ELEMENT_ID" => $_POST['id'],	// ID элемента
		"ELEMENT_CODE" => "",	// Код элемента
		"SECTION_ID" => "",	// ID раздела
		"SECTION_CODE" => "",	// Код раздела
		"HIDE_NOT_AVAILABLE" => "N",	// Не отображать товары, которых нет на складах
		"PROPERTY_CODE" => array(
			0 => "TASTE",
			1 => "PROIZV",
			2 => "INGRID"
		),	
		"OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "sort",
		"OFFERS_SORT_ORDER2" => "asc",
		"OFFERS_LIMIT" => "0",	// Максимальное количество предложений для показа (0 - все)
		"TEMPLATE_THEME" => "blue",	// Цветовая тема
		"ADD_PICT_PROP" => "-",	// Дополнительная картинка основного товара
		"LABEL_PROP" => "-",	// Свойство меток товара
		"OFFER_ADD_PICT_PROP" => "-",	// Дополнительные картинки предложения
		"OFFER_TREE_PROPS" => array(	// Свойства для отбора предложений
			0 => "-",
		),
		"DISPLAY_NAME" => "Y",	// Выводить название элемента
		"DETAIL_PICTURE_MODE" => "IMG",	// Режим показа детальной картинки
		"ADD_DETAIL_TO_SLIDER" => "N",	// Добавлять детальную картинку в слайдер
		"DISPLAY_PREVIEW_TEXT_MODE" => "E",	// Показ описания для анонса
		"PRODUCT_SUBSCRIPTION" => "N",	// Разрешить оповещения для отсутствующих товаров
		"SHOW_DISCOUNT_PERCENT" => "N",	// Показывать процент скидки
		"SHOW_OLD_PRICE" => "N",	// Показывать старую цену
		"SHOW_MAX_QUANTITY" => "N",	// Показывать общее количество товара
		"SHOW_CLOSE_POPUP" => "N",	// Показывать кнопку продолжения покупок во всплывающих окнах
		"MESS_BTN_BUY" => "Купить",	// Текст кнопки "Купить"
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",	// Текст кнопки "Добавить в корзину"
		"MESS_BTN_SUBSCRIBE" => "Подписаться",	// Текст кнопки "Уведомить о поступлении"
		"MESS_BTN_COMPARE" => "Сравнить",	// Текст кнопки "Сравнить"
		"MESS_NOT_AVAILABLE" => "Нет в наличии",	// Сообщение об отсутствии товара
		"USE_VOTE_RATING" => "N",	// Включить рейтинг товара
		"USE_COMMENTS" => "N",	// Включить отзывы о товаре
		"BRAND_USE" => "N",	// Использовать компонент "Бренды"
		"SECTION_URL" => "",	// URL, ведущий на страницу с содержимым раздела
		"DETAIL_URL" => "",	// URL, ведущий на страницу с содержимым элемента раздела
		"SECTION_ID_VARIABLE" => "SECTION_ID",	// Название переменной, в которой передается код группы
		"CHECK_SECTION_ID_VARIABLE" => "N",	// Использовать код группы из переменной, если не задан раздел элемента
		"CACHE_TYPE" => "N",	// Тип кеширования
		"CACHE_TIME" => "",	// Время кеширования (сек.)
		"CACHE_GROUPS" => "Y",	// Учитывать права доступа
		"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
		"SET_BROWSER_TITLE" => "Y",	// Устанавливать заголовок окна браузера
		"BROWSER_TITLE" => "-",	// Установить заголовок окна браузера из свойства
		"SET_META_KEYWORDS" => "Y",	// Устанавливать ключевые слова страницы
		"META_KEYWORDS" => "-",	// Установить ключевые слова страницы из свойства
		"SET_META_DESCRIPTION" => "Y",	// Устанавливать описание страницы
		"META_DESCRIPTION" => "-",	// Установить описание страницы из свойства
		"SET_STATUS_404" => "N",	// Устанавливать статус 404
		"ADD_SECTIONS_CHAIN" => "Y",	// Включать раздел в цепочку навигации
		"ADD_ELEMENT_CHAIN" => "Y",	// Включать название элемента в цепочку навигации
		"USE_ELEMENT_COUNTER" => "N",	// Использовать счетчик просмотров
		"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
		"PRODUCT_ID_VARIABLE" => "id",	// Название переменной, в которой передается код товара для покупки
		"DISPLAY_COMPARE" => "N",	// Разрешить сравнение товаров
		"PRICE_CODE" => array(	// Тип цены
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",	// Использовать вывод цен с диапазонами
		"SHOW_PRICE_COUNT" => "1",	// Выводить цены для количества
		"PRICE_VAT_INCLUDE" => "Y",	// Включать НДС в цену
		"PRICE_VAT_SHOW_VALUE" => "Y",	// Отображать значение НДС
		"CONVERT_CURRENCY" => "N",	// Показывать цены в одной валюте
		"BASKET_URL" => "/personal/basket.php",	// URL, ведущий на страницу с корзиной покупателя
		"USE_PRODUCT_QUANTITY" => "N",	// Разрешить указание количества товара
		"ADD_PROPERTIES_TO_BASKET" => "Y",	// Добавлять в корзину свойства товаров и предложений
		"PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
		"PARTIAL_PRODUCT_PROPERTIES" => "N",	// Разрешить добавлять в корзину товары, у которых заполнены не все характеристики
		"PRODUCT_PROPERTIES" => "",	// Характеристики товара
		"OFFERS_CART_PROPERTIES" => "",	// Свойства предложений, добавляемые в корзину
		"ADD_TO_BASKET_ACTION" => array(	// Показывать кнопки добавления в корзину и покупки
			0 => "BUY",
		),
		"LINK_IBLOCK_TYPE" => "",	// Тип инфоблока, элементы которого связаны с текущим элементом
		"LINK_IBLOCK_ID" => "",	// ID инфоблока, элементы которого связаны с текущим элементом
		"LINK_PROPERTY_SID" => "",	// Свойство, в котором хранится связь
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",	// URL на страницу, где будет показан список связанных элементов
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
	),
	false
);
}
elseif(!empty($_POST['id']) && !empty($_POST['del'])){
	unset($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'][$_POST['id']]);?>
	<?if(!empty($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'])):?>
	Porovnať <?=count($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'])?>
	<?else:?>
	Porovnať
	<?endif?>
<?}
else{?>
	<?if(!empty($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'])):?>
	Porovnať <?=count($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'])?>
	<?else:?>
	Porovnať
	<?endif?>
<?}
?>
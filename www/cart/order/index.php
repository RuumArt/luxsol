<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Dokončenie objednávky");
?>
<main class="content">
<div class="content-images">
 <img src="/local/templates/luxol/images/img-1.png" alt="" class="image-1"> <img src="/local/templates/luxol/images/img-2.png" alt="" class="image-2">
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
<div class="cart">
	<div class="container">
          <?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.ajax", 
	"order", 
	array(
		"COMPONENT_TEMPLATE" => "order",
		"PAY_FROM_ACCOUNT" => "N",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"ALLOW_AUTO_REGISTER" => "Y",
		"ALLOW_APPEND_ORDER" => "Y",
		"SEND_NEW_USER_NOTIFY" => "N",
		"DELIVERY_NO_AJAX" => "H",
		"SHOW_NOT_CALCULATED_DELIVERIES" => "L",
		"DELIVERY_NO_SESSION" => "Y",
		"TEMPLATE_LOCATION" => ".default",
		"SPOT_LOCATION_BY_GEOIP" => "Y",
		"DELIVERY_TO_PAYSYSTEM" => "d2p",
		"SHOW_VAT_PRICE" => "N",
		"USE_PREPAYMENT" => "N",
		"COMPATIBLE_MODE" => "Y",
		"USE_PRELOAD" => "N",
		"ALLOW_USER_PROFILES" => "N",
		"ALLOW_NEW_PROFILE" => "N",
		"TEMPLATE_THEME" => "site",
		"SHOW_ORDER_BUTTON" => "final_step",
		"SHOW_TOTAL_ORDER_BUTTON" => "N",
		"SHOW_PAY_SYSTEM_LIST_NAMES" => "Y",
		"SHOW_PAY_SYSTEM_INFO_NAME" => "Y",
		"SHOW_DELIVERY_LIST_NAMES" => "Y",
		"SHOW_DELIVERY_INFO_NAME" => "Y",
		"SHOW_DELIVERY_PARENT_NAMES" => "Y",
		"SHOW_STORES_IMAGES" => "Y",
		"SKIP_USELESS_BLOCK" => "Y",
		"BASKET_POSITION" => "after",
		"SHOW_BASKET_HEADERS" => "N",
		"DELIVERY_FADE_EXTRA_SERVICES" => "N",
		"SHOW_COUPONS_BASKET" => "N",
		"SHOW_COUPONS_DELIVERY" => "N",
		"SHOW_COUPONS_PAY_SYSTEM" => "Y",
		"SHOW_NEAREST_PICKUP" => "N",
		"DELIVERIES_PER_PAGE" => "9",
		"PAY_SYSTEMS_PER_PAGE" => "9",
		"PICKUPS_PER_PAGE" => "5",
		"SHOW_PICKUP_MAP" => "N",
		"SHOW_MAP_IN_PROPS" => "N",
		"PICKUP_MAP_TYPE" => "yandex",
		"PROPS_FADE_LIST_1" => array(
		),
		"USER_CONSENT" => "Y",
		"USER_CONSENT_ID" => "1",
		"USER_CONSENT_IS_CHECKED" => "N",
		"USER_CONSENT_IS_LOADED" => "N",
		"ACTION_VARIABLE" => "soa-action",
		"PATH_TO_BASKET" => "/cart/",
		"PATH_TO_PERSONAL" => "index.php",
		"PATH_TO_PAYMENT" => "",
		"PATH_TO_AUTH" => "/auth/",
		"SET_TITLE" => "Y",
		"DISABLE_BASKET_REDIRECT" => "N",
		"EMPTY_BASKET_HINT_PATH" => "/",
		"USE_PHONE_NORMALIZATION" => "Y",
		"PRODUCT_COLUMNS_VISIBLE" => array(
			0 => "PREVIEW_PICTURE",
			1 => "PROPS",
		),
		"ADDITIONAL_PICT_PROP_1" => "-",
		"ADDITIONAL_PICT_PROP_6" => "-",
		"BASKET_IMAGES_SCALING" => "adaptive",
		"SERVICES_IMAGES_SCALING" => "adaptive",
		"PRODUCT_COLUMNS_HIDDEN" => array(
		),
		"HIDE_ORDER_DESCRIPTION" => "N",
		"USE_YM_GOALS" => "N",
		"USE_ENHANCED_ECOMMERCE" => "N",
		"USE_CUSTOM_MAIN_MESSAGES" => "Y",
		"USE_CUSTOM_ADDITIONAL_MESSAGES" => "Y",
		"USE_CUSTOM_ERROR_MESSAGES" => "Y",
		"NO_PERSONAL" => "N",
		"PROPS_FADE_LIST_2" => array(
		),
		"MESS_AUTH_BLOCK_NAME" => "Povolenie",
		"MESS_REG_BLOCK_NAME" => "Registrácia",
		"MESS_BASKET_BLOCK_NAME" => "Tovar v košíku",
		"MESS_REGION_BLOCK_NAME" => "Typ platiteľa",
		"MESS_PAYMENT_BLOCK_NAME" => "Platba",
		"MESS_DELIVERY_BLOCK_NAME" => "Doprava",
		"MESS_BUYER_BLOCK_NAME" => "Fakturačná / dodacia adresa",
		"MESS_BACK" => "Späť",
		"MESS_FURTHER" => "Ďalej",
		"MESS_EDIT" => "zmena",
		"MESS_ORDER" => "Odoslať objednávku s povinnosťou platby",
		"MESS_PRICE" => "Náklady",
		"MESS_PERIOD" => "Dodacia lehota",
		"MESS_NAV_BACK" => "Späť",
		"MESS_NAV_FORWARD" => "Tešiť",
		"MESS_PRICE_FREE" => "zadarmo",
		"MESS_ECONOMY" => "Úspora",
		"MESS_REGISTRATION_REFERENCE" => "Ak je to váš prvý čas na webe a chcete, aby sme vás pamätať a udržať všetky svoje objednávky, vyplňte registračný formulár.",
		"MESS_AUTH_REFERENCE_1" => "Symbol hviezdičky ( * ) označuje povinné polia.",
		"MESS_AUTH_REFERENCE_2" => "Po registrácii dostanete informačný list.",
		"MESS_AUTH_REFERENCE_3" => "Osobné informácie získané k dispozícii on-line obchode pri registrácii alebo akýmkoľvek iným spôsobom nebudú poskytnuté tretím stranám bez súhlasu užívateľov, okrem prípadov, v ktorých to vyžaduje zákon, alebo rozhodnutie súdu.",
		"MESS_ADDITIONAL_PROPS" => "Dodatočný majetok",
		"MESS_USE_COUPON" => "Použiť kupón",
		"MESS_COUPON" => "Kupón",
		"MESS_PERSON_TYPE" => "Typ platiteľa",
		"MESS_SELECT_PROFILE" => "Vybraný profil",
		"MESS_REGION_REFERENCE" => "Vyberte svoje mesto zo zoznamu. Ak nemôžete nájsť svoje mesto, zvoľte \"iné miesto\" a zadajte mesto do poľa \" Mesto\"",
		"MESS_PICKUP_LIST" => "Body vyzdvihnutia:",
		"MESS_NEAREST_PICKUP_LIST" => "Najbližší bod:",
		"MESS_SELECT_PICKUP" => "Vybrať",
		"MESS_INNER_PS_BALANCE" => "Na vašom používateľskom účte:",
		"MESS_ORDER_DESC" => "Pripomienky k objednávke:",
		"MESS_SUCCESS_PRELOAD_TEXT" => "Вы заказывали в нашем интернет-магазине, поэтому мы заполнили все данные автоматически.<br />Если все заполнено верно, нажмите кнопку \"#ORDER_BUTTON#\".",
		"MESS_FAIL_PRELOAD_TEXT" => "Objednali ste si v našom internetovom obchode, takže sme vyplnili všetky dáta automaticky.< br />dávajte pozor na rozšírený blok s informáciami o objednávke. Tu môžete vykonať potrebné zmeny alebo nechať tak, ako je, a kliknite na tlačidlo \" # ORDER_BUTTON#\".",
		"MESS_DELIVERY_CALC_ERROR_TITLE" => "Nepodarilo sa vypočítať náklady na dopravu.",
		"MESS_DELIVERY_CALC_ERROR_TEXT" => "Môžete pokračovať v zadávaní objednávky a neskôr vás manažér obchodu bude kontaktovať a objasniť informácie o doručení.",
		"MESS_PAY_SYSTEM_PAYABLE_ERROR" => "Budete môcť zaplatiť za objednávku po tom, čo manažér skontroluje dostupnosť úplnej sady produktov na sklade. Ihneď po overení dostanete e-mail s platobnými pokynmi. Za objednávku môžete zaplatiť v Osobnej časti stránky.",
		"SHOW_COUPONS" => "Y",
		"DATA_LAYER_NAME" => "dataLayer",
		"BRAND_PROPERTY" => ""
	),
	false
);?>
	</div>
</div>
 </main>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
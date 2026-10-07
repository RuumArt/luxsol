<? $message = \COption::GetOptionString( "askaron.settings", "UF_MESSAGE"); ?>
<!DOCTYPE html>
<html lang="sk">
<head>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="mailru-domain" content="6cwmvuOCVsCEqZ8R" />
	<?$APPLICATION->ShowMeta("robots")?>
	<?$APPLICATION->ShowCSS()?>
	<?$APPLICATION->ShowHeadStrings()?>
	<?$APPLICATION->ShowHeadScripts()?>
	<?$APPLICATION->ShowMeta("description")?>
	<title><? $APPLICATION->ShowTitle(); ?></title>
	<?php
	// Канонический адрес: тот же путь без фильтров, сортировки и меток кампаний
	$roomCanonical = function_exists('room_canonical_url') ? room_canonical_url() : '';
	if ($roomCanonical !== ''):
	?>
	<link rel="canonical" href="<?=htmlspecialcharsbx($roomCanonical)?>" />
	<?php endif; ?>
	<?// Иконки сайта: знак логотипа без надписи, на белом фоне.
	// В мелких размерах штрихи сливаются, поэтому 16-48px нарисованы
	// плотным силуэтом в цветах логотипа, а 180 и 192px - как в логотипе.
	// ?v=2 нужен, чтобы браузеры забрали новые файлы: старую иконку
	// они кешируют надолго ?>
	<link rel="icon" href="/favicon.ico?v=2" sizes="any" />
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2" />
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=2" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2" />
	
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/swiper.min.css?v=05042023">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/jquery.fancybox.min.css?v=05042023">
  <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/swiper.min.css?v=05042023">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/magnify.css?v=05042023">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/intlTelInput.min.css?v=05042023">
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/style.css?v=05042023">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css?v=13052021" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/custom.min.css?v=05042023">
	<?/* Был в конце footer.php: страница успевала отрисоваться без него, и поля
	   форм, кнопки, тексты на мгновение показывались без оформления.
	   Подключаем последним из стилей - порядок применения прежний */?>
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/build/css/all.css?v=05042023">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
	
	
	<script src="<?=SITE_TEMPLATE_PATH?>/build/js/scripts.js"></script>

	<?if(stristr($_SERVER['REQUEST_URI'], 'catalog') == true) {?>
	    <?
		CModule::IncludeModule("iblock");
		if(!empty($_REQUEST['COD'])){
			if(stristr($_REQUEST['COD'], '/') == true) {
				$rr = explode("/", $_REQUEST['COD']);
			}
			foreach ($rr as $r){
				$_REQUEST['COD'] = $r;
			}
			$CODE = $_REQUEST['COD'];
		}
		$arFilter = array('IBLOCK_ID' => 1, 'CODE' => $CODE); // выберет потомков без учета активности
		$rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter, false, array("UF_*"), false);
		while ($arSect = $rsSect->GetNext())
		{
		   $ID = $arSect['ID'];
		}
	    if(!empty($ID)){
	    	$i = 0;
			$arFilter = array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => $ID, "INCLUDE_SUBSECTIONS" => "Y",);
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("ID", "IBLOCK_ID"));
			while($ar_fields = $res->GetNext())
			{
			 $i++;
			}
	    }
		?>
	    <?if(!empty($_GET['count'])){
	    	$to = $_GET['count'];
	    }
	    else{
	    	$to = 20;
	    }
	    ?>
		<?if($i > $to):?>
			<?$pageall = ceil($i/15);?>
			<?if($_GET['PAGEN_1'] == 2):?>
			<link rel="prev" href="<?=room_page_url()?>" />
			<?elseif(!empty($_GET['PAGEN_1'])):?>
			<?$page = $_GET['PAGEN_1']-1;?>
			<link rel="prev" href="<?=room_page_url()?>?PAGEN_1=<?=$page?>" />
			<?endif?>
			
			<?if(empty($_GET['PAGEN_1'])):?>
			<link rel="next" href="<?=room_page_url()?>?PAGEN_1=2" />
			<?elseif(!empty($_GET['PAGEN_1']) && ($pageall != $_GET['PAGEN_1'])):?>
			<?$page = $_GET['PAGEN_1']+1;?>
			<link rel="next" href="<?=room_page_url()?>?PAGEN_1=<?=$page?>" />
			<?endif?>
		<?endif?>
	<?}?>
	<?// Старый canonical выводился только на страницах пагинации, по http
	// и вёл на первую страницу. Теперь адрес ставится выше, в одном месте
	// для всех страниц ?>

	<script>
  // Define dataLayer and the gtag function.
  window.dataLayer = window.dataLayer || [];
  function gtag(){ dataLayer.push(arguments); }

	gtag('set', 'url_passthrough', true);
  
	// Выбор посетителя хранится в localStorage под ключом cookie-settings,
	// его пишет баннер согласия. Раньше состояние по умолчанию всегда было
	// "запрещено", а разрешение приходило из подвала - уже после загрузки
	// GTM. Из-за этого у вернувшегося посетителя первый просмотр страницы
	// уходил в Google как обезличенный, и реклама теряла привязку к клику.
	// Теперь сохранённый выбор выставляется здесь, до контейнера
	var roomConsent = {
		'ad_storage': 'denied',
		'ad_user_data': 'denied',
		'ad_personalization': 'denied',
		'analytics_storage': 'denied'
	};

	try {
		var roomSaved = JSON.parse(localStorage.getItem('cookie-settings') || '{}');

		if (roomSaved.analytics) {
			roomConsent['analytics_storage'] = 'granted';
		}

		if (roomSaved.marketing) {
			roomConsent['ad_storage'] = 'granted';
			roomConsent['ad_user_data'] = 'granted';
			roomConsent['ad_personalization'] = 'granted';
		}

		// Пока посетитель не ответил, теги ждут его решения и не шлют
		// обезличенных обращений: за 500 мс он обычно успевает нажать кнопку
		if (!localStorage.getItem('hasCookies')) {
			roomConsent['wait_for_update'] = 500;
		}
	} catch (e) {
		// Приватный режим или заблокированное хранилище: остаёмся на запрете.
		// Обращение к localStorage обязано быть внутри try: если оно бросит
		// исключение снаружи, скрипт оборвётся до consent default, и теги
		// Google будут считать, что согласие получено
	}

	gtag('consent', 'default', roomConsent);
	</script>

	<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-W3ZB6GV');</script>
<!-- End Google Tag Manager -->
 
</head>
<body>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W3ZB6GV"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
 
<?=$APPLICATION->ShowPanel();?>

<header class="header m-hide">
	<? if(!empty($message) && $APPLICATION->GetCurDir() === '/'): ?>
		<div class="alert alert-message"><?=$message?></div>
	<? endif; ?>
	<div class="header-top">
		<div class="container">
			<div class="header-top__left">
				<?$APPLICATION->IncludeComponent("bitrix:menu", "top", Array(
					"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
						"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
						"DELAY" => "N",	// Откладывать выполнение шаблона меню
						"MAX_LEVEL" => "1",	// Уровень вложенности меню
						"MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
							0 => "",
						),
						"MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
						"MENU_CACHE_TYPE" => "N",	// Тип кеширования
						"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
						"ROOT_MENU_TYPE" => "top",	// Тип меню для первого уровня
						"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
					),
					false
				);?>
			</div>
			<div class="header-top__right">
				<ul class="contact">
					<li class="newmail">
						<?$APPLICATION->IncludeFile("/include/mail.php")?>
					</li>
					<li class="newmail newmail--tel">
						<?$APPLICATION->IncludeFile("/include/phone.php")?>
					</li>
					<li>
						<a href="" class="callback btn btn-success" data-target="callback">Objednať hovor</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="header-main">
		<div class="container">
			<div class="header-main__logo">
				<?if($APPLICATION->GetCurPage() !== "/"):?>
				<a href="/">
					<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
				</a>
				<?else:?>
					<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
				<?endif?>
			</div>
			<div class="header-main__right">
				<form action="/search/" class="header-main__search">
					<input type="text" class="form-control" name="q" value="<?=htmlspecialcharsbx($_GET['q'])?>" placeholder="Vyhľadávanie na webe">
					<button type="submit" class="btn btn-success">
						<img src="<?=SITE_TEMPLATE_PATH?>/images/search.png" alt="">
						<span>Hľadanie</span>
					</button>
				</form>
				<ul class="header-main__options">
					<li class="favorite">
						<a href="/favorites/" class="btn btn-default">
							<img src="<?=SITE_TEMPLATE_PATH?>/images/favorite.png" alt="">
							<span class="icon-heart">
							<?
							$fav = 0;
							$yes = $APPLICATION->get_cookie("favorits");
							$mas = json_decode($yes,true);
							foreach ($mas as $v){
								if(!empty($v)){
									$fav++;
								}
							}
							?>
							<?=$fav?>
							</span>	
						</a>
					</li>
					<li>
						<? $srav_count = $_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS']; ?>
						<a href="/comparison/" class="btn btn-default">
							<img src="<?=SITE_TEMPLATE_PATH?>/images/graphic.png" alt="">
							<? $srav = count((array) $srav_count); ?>
							<span class="icon-rating">Porovnať <?if(!empty($srav)):?><?=$srav?><?endif?></span>
						</a>
					</li>
					<li class="headerbasket">
						 <?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.small", "small_header_mob", Array(
				                		"PATH_TO_BASKET" => "/personal/basket.php",
				                		"PATH_TO_ORDER" => "/personal/order.php",
				                		"SHOW_DELAY" => "N",
				                		"SHOW_NOTAVAIL" => "Y",
				                		"SHOW_SUBSCRIBE" => "Y"
										),
										false
									);?>
						
					</li>											
				</ul>
			</div>
			<a href="#menu" class="nav-toggle js-get-pane">
				<span class="bar bar-1"></span>
				<span class="bar bar-2"></span>
				<span class="bar bar-3"></span>
			</a>
		</div>
	</div>
	<div class="header-nav">
		<div class="container">
			<div class="header-nav__category">
				<a href="" class="category-btn">
					<span class="bars">
						<span class="bar bar-1"></span>
						<span class="bar bar-2"></span>
						<span class="bar bar-3"></span>
					</span>
					<span class="text">VŠETKY KATEGÓRIE</span>
				</a>
				<?$APPLICATION->IncludeComponent("bitrix:menu", "catalog", Array(
					"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
						"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
						"DELAY" => "N",	// Откладывать выполнение шаблона меню
						"MAX_LEVEL" => "3",	// Уровень вложенности меню
						"MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
							0 => "",
						),
						"MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
						"MENU_CACHE_TYPE" => "N",	// Тип кеширования
						"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
						"ROOT_MENU_TYPE" => "left",	// Тип меню для первого уровня
						"USE_EXT" => "Y",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
					),
					false
				);?>
			</div>
				<?$APPLICATION->IncludeComponent("bitrix:menu", "top", Array(
					"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
						"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
						"DELAY" => "N",	// Откладывать выполнение шаблона меню
						"MAX_LEVEL" => "1",	// Уровень вложенности меню
						"MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
							0 => "",
						),
						"MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
						"MENU_CACHE_TYPE" => "N",	// Тип кеширования
						"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
						"ROOT_MENU_TYPE" => "top2",	// Тип меню для первого уровня
						"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
						"CLASS" => "header-nav__list"
					),
					false
				);?>
		</div>
	</div>
</header>

<div class="mobile-pane" id="menu">
	<div class="mobile-pane__header">
		<span>Menu</span>
		<button type="button" class="mobile-pane__close"><svg viewBox="0 0 24 24"><path fill-rule="evenodd" d="M18.997 6.417l-1.414-1.414L12 10.586 6.417 5.003 5.003 6.417 10.586 12l-5.583 5.583 1.414 1.414L12 13.414l5.583 5.583 1.414-1.414L13.414 12z"></path></svg></button>
	</div>
	<div class="mobile-pane__body">
		<div class="mobile-pane__catalog">
			<a href="#catalog" class="m-toggle-catalog js-get-pane"><span class="bars">
						<span class="bar bar-1"></span>
						<span class="bar bar-2"></span>
						<span class="bar bar-3"></span>
					</span><span>VŠETKY KATEGÓRIE</span></a>
		</div>
		<nav class="mobile-pane__nav">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "top-mobile", Array(
				"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
					"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
					"DELAY" => "N",	// Откладывать выполнение шаблона меню
					"MAX_LEVEL" => "1",	// Уровень вложенности меню
					"MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
						0 => "",
					),
					"MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
					"MENU_CACHE_TYPE" => "N",	// Тип кеширования
					"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
					"ROOT_MENU_TYPE" => "top3",	// Тип меню для первого уровня
					"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
					"CLASS" => "mobile-menu"
				),
				false
			);?>
		</nav>
		<div class="mobile-pane__contacts n-contacts">
			<div class="n-contacts__item">
				<a href="tel:+421944627014" class="n-contacts__link n-contacts__link--phone"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.0318 6.06568C1.0318 6.13639 1.01767 6.29193 1.00353 6.51817C0.989401 6.7727 1.01767 7.14034 1.08833 7.60697C1.15899 8.08774 1.31445 8.65335 1.52643 9.31794C1.73842 9.98253 2.06347 10.732 2.48744 11.5521C2.92555 12.3722 3.49085 13.2914 4.2116 14.2529C4.91823 15.2286 5.82271 16.2608 6.91091 17.3213C8.28176 18.7071 9.58195 19.7959 10.7691 20.5877C11.9703 21.3796 13.0585 21.9735 14.0054 22.3694C14.9382 22.7653 15.7437 23.0057 16.3655 23.1047C17.0156 23.2037 17.482 23.2602 17.7647 23.2602C17.8777 23.2602 17.9625 23.2602 18.0332 23.2461C18.0897 23.2461 18.1321 23.2319 18.1604 23.2319C18.33 23.2037 18.5137 23.1471 18.6974 23.0623C18.8811 22.9774 19.0224 22.8643 19.1355 22.7512C20.3073 21.5892 20.8201 20.4692 21.709 19.0664L16.709 15.5664C16.1556 16.1636 14.6979 17.5476 14.6979 17.5476C14.6979 17.5476 12.0187 17.3762 9.20898 14.5664C6.20898 11.5664 6.64239 9.50177 6.64239 9.50177C6.64239 9.50177 8.11433 8.07939 8.70898 7.56641C8.70898 7.56641 5.61572 3.57668 4.70898 2.56641C4.70898 2.56641 2.70898 4.06641 1.52643 5.09001C1.39924 5.21727 1.28618 5.35867 1.20139 5.5425C1.11659 5.71218 1.06006 5.896 1.0318 6.06568Z" stroke="#828282" stroke-miterlimit="10"></path>
                            <path d="M18.8596 10.6794C18.8544 9.83079 18.6687 9.08194 18.3552 8.3536C17.9557 7.49749 17.3756 6.74114 16.712 6.28135C16.0263 5.68353 15.1931 5.39251 14.4043 5.37752C13.946 5.38029 13.4349 5.46231 13.0099 5.6721M22.6614 11.9097C22.9263 9.8852 22.5412 7.89418 21.7532 6.25097C21.1484 4.9323 20.2463 3.80291 19.1968 2.98032C18.1362 2.08872 16.9283 1.50392 15.648 1.28467C14.2288 1.01691 12.7483 1.18372 11.3036 1.98187" stroke="#828282" stroke-miterlimit="22.9256"></path>
						</svg><span>+421 944-627-014</span></a>
			</div>
			<div class="n-contacts__item">
				<a href="mailto:office@luxsol.sk" class="n-contacts__link n-contacts__link--mail"><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.7622 9.96671L15.396 16.4502M1 24H24V9.62321L12.5 1L1 9.62321V24ZM24 24L12.5 14.3606L1 24H24ZM1.24483 9.96671L9.61101 16.4502L1.24483 9.96671Z" stroke="#828282" stroke-miterlimit="22.9256" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>office@luxsol.sk</span></a>
			</div>
		</div>
		<div class="mobile-pane__socials">
			<ul class="socials">
				<li class="socials__item"><a href="https://www.facebook.com/luxsolsro/" class="socials__link socials__link---fb" target="_blank"><svg width="14" height="27" viewBox="0 0 14 27" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.54531 27H3.76002V13.4983H0.869141V8.84606H3.76002V6.05302C3.76002 2.25795 5.39149 0 10.0296 0H13.89V4.65394H11.4773C9.67169 4.65394 9.55238 5.30406 9.55238 6.51739L9.54442 8.84606H13.9165L13.4048 13.4983H9.54442L9.54531 27Z" fill="#3578E5"></path></svg></a></li>
				<li class="socials__item"><a href="https://www.instagram.com/luxsol_sk" target="_blank" class="socials__link socials__link---inst"><svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M12.0304 0C9.01192 0 8.63346 0.0126627 7.44799 0.0661955C6.26494 0.119641 5.45704 0.305565 4.75003 0.577508C4.01916 0.85862 3.39933 1.23475 2.78145 1.84627C2.16356 2.45779 1.78352 3.07123 1.49949 3.79458C1.22472 4.49431 1.03686 5.29389 0.982856 6.46476C0.928766 7.63798 0.916016 8.01258 0.916016 11C0.916016 13.9875 0.928766 14.3621 0.982856 15.5353C1.03686 16.7062 1.22472 17.5057 1.49949 18.2055C1.78352 18.9288 2.16356 19.5423 2.78145 20.1538C3.39933 20.7653 4.01916 21.1414 4.75003 21.4225C5.45704 21.6945 6.26494 21.8804 7.44799 21.9338C8.63346 21.9874 9.01192 22 12.0304 22C15.049 22 15.4275 21.9874 16.6129 21.9338C17.7959 21.8804 18.6038 21.6945 19.3108 21.4225C20.0417 21.1414 20.6615 20.7653 21.2794 20.1538C21.8973 19.5423 22.2774 18.9288 22.5614 18.2055C22.8362 17.5057 23.024 16.7062 23.078 15.5353C23.1321 14.3621 23.1449 13.9875 23.1449 11C23.1449 8.01258 23.1321 7.63798 23.078 6.46476C23.024 5.29389 22.8362 4.49431 22.5614 3.79458C22.2774 3.07123 21.8973 2.45779 21.2794 1.84627C20.6615 1.23475 20.0417 0.85862 19.3108 0.577508C18.6038 0.305565 17.7959 0.119641 16.6129 0.0661955C15.4275 0.0126627 15.049 0 12.0304 0" fill="#f241bc"></path>
									<path fill-rule="evenodd" clip-rule="evenodd" d="M11.916 6C9.1546 6 6.91602 8.23859 6.91602 11C6.91602 13.7614 9.1546 16 11.916 16C14.6774 16 16.916 13.7614 16.916 11C16.916 8.23859 14.6774 6 11.916 6Z" fill="#FFF"></path>
									<path fill-rule="evenodd" clip-rule="evenodd" d="M19.916 4.99997C19.916 6.10455 19.0206 7 17.916 7C16.8115 7 15.916 6.10455 15.916 4.99997C15.916 3.89539 16.8115 3 17.916 3C19.0206 3 19.916 3.89539 19.916 4.99997Z" fill="#FFF"></path>
								</svg></a></li>
			</ul>
		</div>
	</div>
</div>

<div class="mobile-pane mobile-pane--top catalog-pane" id="catalog">
	<div class="mobile-pane__header">
		<button type="button" class="catalog-pane__back"><svg viewBox="0 0 24 24" id="icons_corner-left"><path fill-rule="evenodd" d="M13.993 19.997L5.996 12l7.997-7.997 1.414 1.414L8.824 12l6.583 6.583z"></path></svg></button>
		<span>VŠETKY KATEGÓRIE</span>
		<button type="button" class="mobile-pane__close"><svg viewBox="0 0 24 24"><path fill-rule="evenodd" d="M18.997 6.417l-1.414-1.414L12 10.586 6.417 5.003 5.003 6.417 10.586 12l-5.583 5.583 1.414 1.414L12 13.414l5.583 5.583 1.414-1.414L13.414 12z"></path></svg></button>
	</div>
	<div class="mobile-pane__body">
		<div class="catalog-pane__nav">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "catalog-mobile", Array(
					"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
						"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
						"DELAY" => "N",	// Откладывать выполнение шаблона меню
						"MAX_LEVEL" => "3",	// Уровень вложенности меню
						"MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
							0 => "",
						),
						"MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
						"MENU_CACHE_TYPE" => "N",	// Тип кеширования
						"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
						"ROOT_MENU_TYPE" => "left",	// Тип меню для первого уровня
						"USE_EXT" => "Y",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
					),
					false
			);?>
		</div>
	</div>
</div>

<!-- mobile header -->

<div class="header-mobile">
	<a href="#menu" class="header-mobile__burger js-get-pane">
		<span></span>
		<span></span>
		<span></span>
	</a>
	<div class="header-mobile__logo">
		<?if($APPLICATION->GetCurPage() !== "/"):?>
		<a href="/">
			<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
		</a>
		<?else:?>
			<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
		<?endif?>
	</div>
	<div class="header-mobile__cart">
		<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket.small", "small_header_mob", Array(
			"PATH_TO_BASKET" => "/personal/basket.php",
			"PATH_TO_ORDER" => "/personal/order.php",
			"SHOW_DELAY" => "N",
			"SHOW_NOTAVAIL" => "Y",
			"SHOW_SUBSCRIBE" => "Y"
			),
			false
		);?>	
	</div>
</div>

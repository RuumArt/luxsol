<footer class="footer">
	<div class="container">
		<div class="footer__left">
			<?if($APPLICATION->GetCurPage() !== "/"):?>
			<a href="/" class="footer-logo">
				<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
			</a>
			<?else:?>
				<img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" alt="">
			<?endif?>

			<div class="footer__partner">
				<a href="https://mall.sk/partner/luxsol-sk" target="_blank" rel="noopener" action="" class="footer__partner-link"><img src="https://i.cdn.nrholding.net/document/46896812" width="202" height="74" alt="Najdete nás i na MALL.SK"></a>
			</div>
		</div>
		<div class="footer__right">
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
						"ROOT_MENU_TYPE" => "bottom",	// Тип меню для первого уровня
						"USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
						"CLASS" => "footer-nav"
					),
					false
				);?>
			<div class="footer-contacts">
				<ul class="footer-contacts__list">
					<li>
						<?$APPLICATION->IncludeFile("/include/phone.php")?>
					</li>
					<li>
						<?$APPLICATION->IncludeFile("/include/mail.php")?>
					</li>	
					<li class="location">
						<?$APPLICATION->IncludeFile("/include/adres.php")?>
					</li>		
					<li>
						<a class="text" href="/html.php">Mapa stránok</a>
					</li>								
				</ul>
				<ul class="footer-social">
					<?$APPLICATION->IncludeFile("/include/soc.php")?>
				</ul>
			</div>
		</div>
	</div>
</footer>

<?/*
<div class="fixed-file">
	<a href="" class="fixed-file__icon" download>
		<img src="<?=SITE_TEMPLATE_PATH?>/images/file.png" alt="">
	</a>
</div>*/?>

<div class="modal-overlay" id="callback">
	<div class="modal">
		<div class="modal-close"></div>
		<h2 class="modal-title">Napíšte nám</h2>
		<p class="modal-description">Objednajte hovor a naši odborníci Vám zavolajú čoskoro!</p>
		<div id="rescon">
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

<?/* Odstúpenie od zmluvy: ссылки на /odstupenie-od-zmluvy/ открывают форму здесь.
   На самой странице формы окно не выводим - форма там уже есть */?>
<?if ($APPLICATION->GetCurPage() !== "/odstupenie-od-zmluvy/"):?>
<div class="modal-overlay" id="withdrawal">
	<div class="modal">
		<div class="modal-close"></div>
		<h2 class="modal-title">Odstúpenie od zmluvy</h2>
		<div class="withdrawal-form">
			<?$withdrawalFormPrefix = "wdm"; include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/withdrawal_form.php";?>
		</div>
	</div>
</div>
<?endif?>

<div class="modal-overlay" id="cart">
	<div class="modal">
		<div class="modal-close"></div>
		<h2 class="modal-title">Produkt v nákupnom košíku!</h2>
		<div class="modal_button_wrap">
			<a href="/cart/" class="btn btn-success">Prejsť do košíka</a>
			<a href="" class="btn buy-click close-bb" style="background-color:#479ddb;">Pokračovať v nákupe</a>
		</div>

	</div>
</div>

<div class="cookies">
	<div class="cookies__wrapper">
		<div class="cookies__content">
			<h3 class="heading3">Táto webová stránka používa cookies</h3>
			<p class="paragraph">Cookies sú malé textové súbory, ktoré môžu používať webové stránky, aby zefektívnili a zlepšili používateľský zážitok.</p>
			<p class="paragraph">Zákon uvádza, že cookies sa smú na vašom zariadení ukladať bez vášho súhlasu iba v
				tom prípade, ak sú nevyhnutné pre prevádzku stránky. Pre všetky ostatné typy cookies
				je potrebné vaše povolenie.</p>
			<p class="paragraph">Táto stránka používa rôzne typy cookies. Niektoré cookies sú umiestnené službami
				tretích strán, ktoré sa objavujú na našich stránkach. Kedykoľvek môžete zmeniť alebo
				zrušiť svoj súhlas prostredníctvom Vyhlásenia o súboroch cookie na našich webových
				stránkach.</p>
			<p class="paragraph">V dokumente „Ako chránime vaše súkromie“ zistíte viac o tom, kto sme, ako nás
				môžete kontaktovať a ako spracovávame vaše osobné údaje. Pokiaľ nás budete
				kontaktovať ohľadom vášho súhlasu, uveďte, prosím, ID svojho súhlasu a dátum jeho
				udelenia.</p>
		</div>
		<div class="cookies__buttons">
			<button data-type="cookies-popup" type="button" class="button cookies__button">
				Upraviť nastavenia
			</button>
			<button type="button" data-cookies="first" class="button cookies__button js-cookie-accept">
				Prijať nevyhnutné
			</button>
			<button type="button" data-cookies="all" class="button cookies__button js-cookie-accept button__accept">
				Prijať všetko
			</button>
		</div>
	</div>
</div>

<div class="popup" data-popup="cookies-popup" data-close-overlay>
	<div class="popup__wrapper" data-close-overlay>
		<div class="popup__content">
			<button class="button-close popup__close" type="button"></button>
			<div class="popup__body cookies-popup">
				<div class="cookies-popup__top">
					<h3 class="heading3">Detailné nastavenie cookies</h3>
					<p class="paragraph">Tu máte možnosť si prispôsobiť cookies podľa vašich potrieb.</p>
				</div>
				<div class="cookies-popup__content">
					<div class="cookies-details">
						<div class="cookies-details__top">
							<h4 class="heading4">Potrebné cookies</h4>
							<label class="switch switch-disable">
								<input type="checkbox" value="required" class="cookies-switcher switch-input" checked="checked">
								<span class="switch-label"></span>
								<span class="switch-handle"></span>
							</label>
						</div>
						<p class="paragraph cookies-details__content">
							Tieto cookies zabezpečujú správne fungovanie nášho webu. Napríklad funkciu
							prihlásenia sa do používateľského konta alebo ukladanie tovaru do nákupného košíka.
							Pomáhajú nám tiež odhaľovať pokusy o neoprávnené prihlásenie a umožňujú efektívne
							zobrazovanie obsahu.
						</p>
						<div class="accordion cookies-details__more more-details-cookies">
							<div class="accordion__item">
								<div class="accordion__header more-details-cookies__button">Zobraziť detaily</div>
								<div class="accordion__content more-details-cookies__content">
									<div class="more-details-cookies__row">
										<div>Názov</div>
										<div>Poskytovateľ/ <br>Doména</div>
										<div>Vyprší</div>
										<div>Popis</div>
									</div>
									<div class="more-details-cookies__row">
										<div>BX_USER_ID</div>
										<div>.bitrix.info</div>
										<div>59 minút</div>
										<div>
											Ukladá informácie o jedinečnom identifikátore neoprávneného používateľa stránky.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>PHPSESSID</div>
										<div>.luxsol.sk</div>
										<div>počas návštevy</div>
										<div>
											Ukladá informácie o jedinečnom ID relácie používateľa stránky.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>BITRIX_SM_GUEST_ID</div>
										<div>.luxsol.sk</div>
										<div>1 rok</div>
										<div>
											Identifikátor používateľa, ktorý nie je spojený s osobnými údajmi. Doba uchovávania
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>BITRIX_SM_LAST_SETTINGS</div>
										<div>.luxsol.sk</div>
										<div>počas návštevy</div>
										<div>
											Ukladá vlastné nastavenia lokality
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>BITRIX_SM_LAST_VISIT</div>
										<div>.luxsol.sk</div>
										<div>1 rok</div>
										<div>
											Nastaví dátum poslednej návštevy
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="cookies-details">
						<div class="cookies-details__top">
							<h4 class="heading4">Analytické</h4>
							<label class="switch">
								<input type="checkbox" value="analytics" class="cookies-switcher switch-input">
								<span class="switch-label"></span>
								<span class="switch-handle"></span>
							</label>
						</div>
						<p class="paragraph cookies-details__content">
							Tieto cookies nám slúžia na zisťovanie anonymných údajov o návštevnosti nášho webu.
							Môžu hovoriť o tom, odkiaľ ste k nám prišli, o vyhľadávaniach na našom webe, či ako sa
							pohybujete po našej stránke, vďaka čomu ju môžeme neustále zlepšovať. Získané
							údaje nám umožňujú tiež spracovávať štatistiky.
						</p>
						<div class="accordion cookies-details__more more-details-cookies">
							<div class="accordion__item">
								<div class="accordion__header more-details-cookies__button">Zobraziť detaily</div>
								<div class="accordion__content more-details-cookies__content">
									<div class="more-details-cookies__row">
										<div>Názov</div>
										<div>Poskytovateľ/ <br>Doména</div>
										<div>Vyprší</div>
										<div>Popis</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_gid</div>
										<div>.luxsol.sk</div>
										<div>24 hodiny</div>
										<div>Tento názov súboru cookie je spojený s Google Analytics. Tento súbor cookie sa používa na odlíšenie jedinečných používateľov priradením náhodne vygenerovaného čísla ako identifikátora klienta. Je zahrnutá v každej požiadavke na stránku na webe a slúži na výpočet údajov o návštevníkoch, reláciách a kampaniach pre analytické prehľady webových stránok.</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_ga_T1L7E07HYF</div>
										<div>.luxsol.sk</div>
										<div>2 rokov</div>
										<div>
											Tento súbor cookie používa služba Google Analytics na zachovanie stavu relácie.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_ga</div>
										<div>.luxsol.sk</div>
										<div>2 rokov</div>
										<div>
											Tento názov súboru cookie je spojený s Google Analytics. Tento súbor cookie sa používa na odlíšenie jedinečných používateľov priradením náhodne vygenerovaného čísla ako identifikátora klienta. Je zahrnutá v každej požiadavke na stránku na webe a slúži na výpočet údajov o návštevníkoch, reláciách a kampaniach pre analytické prehľady webových stránok.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_gat_gtag_UA_75233349_1</div>
										<div>.luxsol.sk</div>
										<div>10 minút</div>
										<div>
											Používa službu Google Analytics na obmedzenie požiadaviek na jej služby.
										</div>
									</div>

									<div class="more-details-cookies__row">
										<div>_ym_isad</div>
										<div>.luxsol.sk</div>
										<div>2 dni</div>
										<div>
											Slúži na určenie, či má návštevník blokovače reklám.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_ym_d</div>
										<div>.luxsol.sk</div>
										<div>1 rok</div>
										<div>
											Ukladá dátum prvej návštevy návštevníka na stránke.
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_ym_uid</div>
										<div>.luxsol.sk</div>
										<div>1 rok</div>
										<div>
											Umožňuje rozlišovať medzi návštevníkmi
										</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_ym_visorc_*</div>
										<div>.luxsol.sk</div>
										<div>30 minút</div>
										<div>
											Slúži na správne fungovanie Webvisor
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="cookies-details">
						<div class="cookies-details__top">
							<h4 class="heading4">Marketingové</h4>
							<label class="switch">
								<input type="checkbox" value="marketing" class="cookies-switcher switch-input">
								<span class="switch-label"></span>
								<span class="switch-handle"></span>
							</label>
						</div>
						<p class="paragraph cookies-details__content">
							Tieto cookies používame na to, aby sme vám zobrazovali čo najzaujímavejšiu reklamu.
							Slúžia tiež na vykonávanie obdobných marketingových činností prostredníctvom tretích
							strán (cez našich reklamných partnerov a sociálne siete) alebo umožňujú našu účasť v
							sieti affiliate marketingu.
						</p>
						<div class="accordion cookies-details__more more-details-cookies">
							<div class="accordion__item">
								<div class="accordion__header more-details-cookies__button">Zobraziť detaily</div>
								<div class="accordion__content more-details-cookies__content">
									<div class="more-details-cookies__row">
										<div>Názov</div>
										<div>Poskytovateľ/ <br>Doména</div>
										<div>Vyprší</div>
										<div>Popis</div>
									</div>
									<div class="more-details-cookies__row">
										<div>_gcl_au</div>
										<div>.luxsol.sk</div>
										<div>3 mesiace</div>
										<div>
											Tento súbor cookie nastavuje spoločnosť Doubleclick a vykonáva informácie o tom, ako koncový používateľ používa webovú stránku, a o akejkoľvek reklame, ktorú mohol koncový používateľ vidieť pred návštevou uvedenej webovej stránky.
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
				</div>
				<div class="cookies__buttons cookies-popup__buttons">
					<button type="button" class="button cookies__button js-cookie-accept button-close">Uložiť iba vybrané cookies</button>
					<button type="button" class="button cookies__button js-cookie-accept button-close" data-cookies="first">Prijať nevyhnutné</button>
					<button type="button" class="button cookies__button js-cookie-accept button__accept button-close" data-cookies="all">
						Prijať všetko
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?=SITE_TEMPLATE_PATH?>/build/js/jquery.fancybox.min.js"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/packeta.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LfmoGUaAAAAAME-cMHVgyaypMSAz24x0RM2G40O"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/src/js/selectize.js"></script>
<!-- <script src="<?=SITE_TEMPLATE_PATH?>/src/js/lightslider.js"></script> -->
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/jquery.magnify.js?v=12102021"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/swiper.min.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/swiper.min.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/jquery.inputmask.bundle.min.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/jquery-ui.min.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/intlTelInput.min.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/utils.js?v=17052921"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/imask.js"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/all.js?v=03022025"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/build/js/custom.min.js?v=03022025"></script>
	<?$withdrawalJs = SITE_TEMPLATE_PATH . "/build/js/withdrawal-form.js";?>
	<script src="<?=$withdrawalJs?>?v=<?=filemtime($_SERVER["DOCUMENT_ROOT"] . $withdrawalJs)?>"></script>
	<?/* Покупка в GA4 до перехода на оплату картой и досылка недошедшей */?>
	<?$ecommerceJs = SITE_TEMPLATE_PATH . "/build/js/ecommerce.js";?>
	<script src="<?=$ecommerceJs?>?v=<?=filemtime($_SERVER["DOCUMENT_ROOT"] . $ecommerceJs)?>"></script>
	<script>
    (function(w,d,u){
    var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
    var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
    })(window,document,'https://cdn.bitrix24.ru/b9369093/crm/site_button/loader_3_kiwy47.js');
</script>

</body>
</html>

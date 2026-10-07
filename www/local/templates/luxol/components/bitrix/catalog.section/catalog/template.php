<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |	Attention!
 * |	The following comments are for system use
 * |	and are required for the component to work correctly in ajax mode:
 * |	<!-- items-container -->
 * |	<!-- pagination-container -->
 * |	<!-- component-end -->
 */

$this->setFrameMode(true);

?>

<?if(empty($arResult['ITEMS']) && !empty($_GET['set_filter'])):?>
<div class="st_wrapper st_wrapper_no_bg">
<div class="error_page_icon_user">
<img src="<?=SITE_TEMPLATE_PATH?>/images/user_reset_filters_icon.png" alt="">
</div>
<p>Zaškrtli ste príliš veľa kritérií. <br> Pre dané kritériá sa nenašli žiadne výsledky</p>
<a href="<?=$APPLICATION->GetCurPage()?>" class="error_page_reset_filter">Obnoviť filtre.</a>
</div>
<?endif?>
<?$count = count((array) $arResult['ITEMS'])?>

<div class="product-list">
<?foreach ($arResult['ITEMS'] as $k => $item):?>

<?
	$comp = 0;

	if(!empty($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'])):
	foreach($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'] as $id){
	   if($item['ID'] == $id['ID']){
	      $comp = 1;
	   }
	}
	endif;

	$yes = $APPLICATION->get_cookie("favorits");
	$mas = (array) json_decode($yes, true);
?>

<div class="product-list__item">
	<div class="product-card">
		<? if($item['PREVIEW_PICTURE']['SRC']): ?>
		<div class="product-card__image">
			<a href="<?=$item['DETAIL_PAGE_URL']?>" class="_link"><img src="<?=$item['PREVIEW_PICTURE']['SRC']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($item, 'PREVIEW'))?>"></a>
		</div>
		<? endif; ?>
		<div class="product-card__body">
		<? if(!empty($item['PROPERTIES']['RAZMER']['VALUE']) || !empty($item['PROPERTIES']['DIAMETR']['VALUE']) || !empty($item['PROPERTIES']['MATERIAL']['VALUE'])): ?>
		<div class="product-card__meta">
			<?if(!empty($item['PROPERTIES']['RAZMER']['VALUE'])):?>
			<div class="product-card__meta-item" title="">
				<div class="product-card__meta-icon">
					<svg id="plosh" viewBox="0 0 23 22">
						<path fill="#3c6e87" d="M8 7h13v13H8z"></path><path d="M3 7l.583-.472L3 5.808l-.583.72L3 7zm0 13l-.583.472.583.72.583-.72L3 20zM1.583 9.943l2-2.471-1.166-.944L.417 9l1.166.943zm.834-2.471l2 2.471L5.583 9l-2-2.472-1.166.944zM.417 18l2 2.472 1.166-.944-2-2.471L.417 18zm3.166 2.472l2-2.472-1.166-.943-2 2.471 1.166.944zM2.25 7v13h1.5V7h-1.5zM21 3l.472.583.72-.583-.72-.583L21 3zM8 3l-.472-.583-.72.583.72.583L8 3zm10.057-1.417l2.471 2 .944-1.166-2.472-2-.943 1.166zm2.471.834l-2.471 2L19 5.583l2.47-2-.943-1.166zM10 .417l-2.472 2 .944 1.166 2.471-2L10 .417zM7.528 3.583l2.472 2 .943-1.166-2.471-2-.944 1.166zM21 2.25H8v1.5h13v-1.5z" fill="#3c6e87"></path>
					</svg>
				</div>
				<div class="product-card__meta-title">
					<?=$item['PROPERTIES']['RAZMER']['VALUE']?>
				</div>
			</div>
			<? endif; ?>
			<?if(!empty($item['PROPERTIES']['DIAMETR']['VALUE'])):?>
			<div class="product-card__meta-item" title="">
				<div class="product-card__meta-icon product-card__meta-icon--d" >
					<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
						<path d="M437.004,74.819C388.654,26.571,324.372,0,256,0S123.346,26.571,74.996,74.819C26.634,123.079,0,187.246,0,255.501v0.998
							c0,68.255,26.634,132.422,74.996,180.682C123.346,485.429,187.628,512,256,512s132.654-26.571,181.004-74.819
							C485.366,388.921,512,324.754,512,256.499v-0.998C512,187.246,485.366,123.079,437.004,74.819z M472,256.499
							C472,375.327,375.103,472,256,472S40,375.327,40,256.499v-0.998C40,136.673,136.897,40,256,40s216,96.673,216,215.501V256.499z"
							/>
						<path d="M256,130c-68.925,0-125,56.075-125,125s56.075,125,125,125s125-56.075,125-125S324.925,130,256,130z M256,340c-46.869,0-85-38.131-85-85s38.131-85,85-85s85,38.131,85,85C341,301.869,302.869,340,256,340z"/>
					</svg>
				</div>
				<div class="product-card__meta-title">
					<span>⌀</span> <?=$item['PROPERTIES']['DIAMETR']['VALUE']?>
				</div>
			</div>
			<? endif; ?>
			<?if(!empty($item['PROPERTIES']['MATERIAL']['VALUE'])):?>
			<div class="product-card__meta-item" title="">
				<div class="product-card__meta-icon product-card__meta-icon--m" >
					<svg viewBox="0 0 490.667 490.667">
					<path style="fill:#455A64;" d="M480,192c5.891,0,10.667-4.776,10.667-10.667c0-5.891-4.776-10.667-10.667-10.667h-32V64h32
						c5.891,0,10.667-4.776,10.667-10.667S485.891,42.667,480,42.667h-32v-32C448,4.776,443.224,0,437.333,0s-10.667,4.776-10.667,10.667
						v32H320v-32C320,4.776,315.224,0,309.333,0s-10.667,4.776-10.667,10.667v32H192v-32C192,4.776,187.224,0,181.333,0
						c-5.891,0-10.667,4.776-10.667,10.667v32H64v-32C64,4.776,59.224,0,53.333,0S42.667,4.776,42.667,10.667v32h-32
						C4.776,42.667,0,47.442,0,53.333S4.776,64,10.667,64h32v106.667h-32C4.776,170.667,0,175.442,0,181.333
						C0,187.224,4.776,192,10.667,192h32v106.667h-32C4.776,298.667,0,303.442,0,309.333S4.776,320,10.667,320h32v106.667h-32
						C4.776,426.667,0,431.442,0,437.333S4.776,448,10.667,448h32v32c0,5.891,4.776,10.667,10.667,10.667S64,485.891,64,480v-32h106.667
						v32c0,5.891,4.776,10.667,10.667,10.667c5.891,0,10.667-4.776,10.667-10.667v-32h106.667v32c0,5.891,4.776,10.667,10.667,10.667
						S320,485.891,320,480v-32h106.667v32c0,5.891,4.776,10.667,10.667,10.667S448,485.891,448,480v-32h32
						c5.891,0,10.667-4.776,10.667-10.667s-4.776-10.667-10.667-10.667h-32V320h32c5.891,0,10.667-4.776,10.667-10.667
						s-4.776-10.667-10.667-10.667h-32V192H480z M426.667,64v106.667H320V64H426.667z M298.667,64v106.667H192V64H298.667z M298.667,192
						v106.667H192V192H298.667z M64,64h106.667v106.667H64V64z M64,192h106.667v106.667H64V192z M64,426.667V320h106.667v106.667H64z
						 M192,426.667V320h106.667v106.667H192z M426.667,426.667H320V320h106.667V426.667z M426.667,298.667H320V192h106.667V298.667z"/>
					<path d="M53.333,490.667c-5.891,0-10.667-4.776-10.667-10.667V10.667C42.667,4.776,47.442,0,53.333,0S64,4.776,64,10.667V480
						C64,485.891,59.224,490.667,53.333,490.667z"/>
					<path d="M181.333,490.667c-5.891,0-10.667-4.776-10.667-10.667V10.667C170.667,4.776,175.442,0,181.333,0
						C187.224,0,192,4.776,192,10.667V480C192,485.891,187.224,490.667,181.333,490.667z"/>
					<path d="M309.333,490.667c-5.891,0-10.667-4.776-10.667-10.667V10.667C298.667,4.776,303.442,0,309.333,0S320,4.776,320,10.667V480
						C320,485.891,315.224,490.667,309.333,490.667z"/>
					<path d="M437.333,490.667c-5.891,0-10.667-4.776-10.667-10.667V10.667C426.667,4.776,431.442,0,437.333,0S448,4.776,448,10.667V480
						C448,485.891,443.224,490.667,437.333,490.667z"/>
					<path d="M480,448H10.667C4.776,448,0,443.224,0,437.333s4.776-10.667,10.667-10.667H480c5.891,0,10.667,4.776,10.667,10.667
						S485.891,448,480,448z"/>
					<path d="M480,320H10.667C4.776,320,0,315.224,0,309.333s4.776-10.667,10.667-10.667H480c5.891,0,10.667,4.776,10.667,10.667
						S485.891,320,480,320z"/>
					<path d="M480,192H10.667C4.776,192,0,187.224,0,181.333c0-5.891,4.776-10.667,10.667-10.667H480c5.891,0,10.667,4.776,10.667,10.667
						C490.667,187.224,485.891,192,480,192z"/>
					<path d="M480,64H10.667C4.776,64,0,59.224,0,53.333s4.776-10.667,10.667-10.667H480c5.891,0,10.667,4.776,10.667,10.667
						S485.891,64,480,64z"/>
					</svg>
				</div>
				<div class="product-card__meta-title">
					<?=$item['PROPERTIES']['MATERIAL']['VALUE']?>
				</div>
			</div>
			<?endif?>
		</div>
		<? endif; ?>
		<?  $colors = [];
			if(!empty($item['OFFERS'])):
				$counter = 0;
				foreach($item['OFFERS'] as $key => $offer):
					$colors[$counter]['COLOR'] = $offer['COLOR'];
					$colors[$counter]['NAME'] = $offer['COLOR_NAME'];
					$counter++;
				endforeach;
			elseif(!empty($item['PROPERTIES']['COLOR']['VALUE'])):
				$res = CIBlockElement::GetByID($item['PROPERTIES']['COLOR']['VALUE']);
				if($ar_res = $res->GetNext()){
					$colors[0]['COLOR'] = $ar_res['CODE'];
					$colors[0]['NAME'] = $ar_res['NAME'];
				}
			endif;
		?>
		
		<? if(!empty($colors)): ?>
			<div class="product-card__colors">
				<div class="product-card__colors-title">Farba:</div>
				<div class="product-card__colors-list">
				<? foreach($colors as $color): ?>
					<div class="product-card__color" style="background-color: <?=$color['COLOR']?>;" title="<?=$color['NAME']?>"></div>
				<? endforeach; ?>
				</div>
				<? if(!empty($item['PROPERTIES']['COLOR']['VALUE'])): ?>
					<input type="hidden" class="color-<?=$item['ID']?>" value="<?=$colors[0]['NAME']?>">
				<? endif; ?>
			</div>
		<? endif; ?>
		<? if(!empty($item['PROPERTIES']['PROD_ACCESS']['VALUE'])): ?>
			<div class="product-card__avilable">
				<?=$item['PROPERTIES']['PROD_ACCESS']['VALUE']?>
			</div>
		<? endif; ?>
		<?if(!empty($item['OFFERS'])){
 			$id = array_pop($item['OFFERS'])['ID'];
 		}
 		else{
 			$id = $item['ID'];
 		}
 		?>
		<div class="product-card__prices">
			<div class="product-card__price">
				<? if($item['MIN_PRICE']['DISCOUNT_VALUE'] != $item['PRICES']['BASE']['DISCOUNT_VALUE_NOVAT']): ?>
					<div>
						<span>Cena bez DPH:</span> <?=number_format($item['PRICES']['BASE']['DISCOUNT_VALUE_NOVAT'], 2, ',', ' ');?> &#8364;
					</div>
				<? endif; ?>
		 		<div>
					<span>Cena s DPH:</span> <?=number_format($item['MIN_PRICE']['DISCOUNT_VALUE'], 2, ',', ' ');?> &#8364;
				</div>
			</div>
			<? if($item['MIN_PRICE']['DISCOUNT_DIFF'] > 0): ?>
			<div class="product-card__price product-card__price--old">
				<?=number_format($item['MIN_PRICE']['VALUE'], 2, ',', ' ');?> &#8364;
			</div>
			<? endif; ?>
		</div>
		<a href="<?=$item['DETAIL_PAGE_URL']?>" class="product-card__name">
			<?=$item['NAME']?>
		</a>

		<div class="product-card__info">
			<? if(!empty($item['PROPERTIES']['ARTICLE']['VALUE'])): ?>
			<div class="product-card__articul">
				Číslo výrobku: <span class="sku-value"><?=$item['PROPERTIES']['ARTICLE']['VALUE']?></span>
			</div>
			<? endif; ?>
			
			<div class="product-card__actions">
				<a href="#" class="product-card__like <?= (!in_array($item['ID'], $mas)) ? '' : '_active' ?>" data-id="<?=$item['ID']?>" title="">
					<svg width="28" height="25" viewBox="0 0 28 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        				<path d="M13.982 5.26197C12.8338 3.13098 10.0782 1 7.70527 1C1.42856 1 -1.78634 9.52397 4.10764 14.6677L14.2116 24L24.0859 14.6677C29.7503 9.23004 26.4588 1 20.1821 1C17.8092 1 15.0536 3.13098 13.982 5.26197Z" stroke="white" stroke-width="2" stroke-miterlimit="22.9256" stroke-linecap="round" stroke-linejoin="round"></path>
    				</svg>
				</a>
				<a href="#" class="product-card__compare <?= ($comp !== 1) ? '' : '_active' ?>" data-id="<?=$item['ID']?>" title="">
					<svg xmlns="http://www.w3.org/2000/svg" width="98" height="98" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="3" stroke-linecap="butt" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
				</a>
			</div>
		</div>

		<? if($item['PROPERTIES']['PROD_ACCESS']['VALUE_ENUM_ID'] != 20): ?>
		<div class="product-card__add">
			<div class="product-card__add2cart">
				<?
 				$ar_new_groups = array();
 				$db_old_groups = CIBlockElement::GetElementGroups($item['ID'], true);
				while($ar_group = $db_old_groups->Fetch()){
				    $ar_new_groups[] = $ar_group["ID"];
				}
				if(!in_array(1, $ar_new_groups) && !in_array(46, $ar_new_groups)){
					foreach ($ar_new_groups as $val){
					   $arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], "ACTIVE" => "Y", "ID" => $val);
					   $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
					   while ($arSect = $rsSect->GetNext())
					   {
					       $ar_new_groups[] = $arSect['IBLOCK_SECTION_ID'];
					   }
					}
				}
				if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):
 				?>
 				<a href="<?=$item['DETAIL_PAGE_URL']?>" class="n-btn n-btn--small n-btn--blue">
				 	Podrobnejšie
 				</a>
 				<?else:?>
	 				<?if(!empty($item['PROPERTIES']['NO_ITEM']['VALUE'])):?>
						<p style="color: red">Товар временно недоступен</p>
					<?else:?>
						<a href="#" class="n-btn n-btn--small n-btn--blue" data-target="cart" onclick="add_cart_catalog(this)" data-id="<?=$id?>">
							Do košíka
	 					</a>
	 				<?endif?>
 				<?endif?>
			</div>
			<?
				$quare = false;
				$db_old_groups = CIBlockElement::GetElementGroups($item['ID'], true);
				while($ar_group = $db_old_groups->Fetch()){
				    $ar_new_groups[] = $ar_group["ID"];
				}
				
				if(!in_array(1, $ar_new_groups)){
					foreach ($ar_new_groups as $val){
					   if(!empty($val)){
					   $arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], "ACTIVE" => "Y", "ID" => $val);
					   $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
					   while ($arSect = $rsSect->GetNext())
					   {
					       $ar_new_groups[] = $arSect['IBLOCK_SECTION_ID'];
					   }
					   }
					}
				}
				if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)){
					$quare = true;
				}
			?>
			<?if($quare != true):?>
			<div class="product-card__number">
				<div class="number-input">
					<button class="number-input__button number-input__button--minus"></button>
					<input type="text" class="number-input__el quant-item<?=$id?>" value="1">
					<button class="number-input__button number-input__button--plus"></button>
				</div>
			</div>
			<? endif; ?>
		</div>
		<? endif; ?>
		</div>
	</div>
</div>

<?endforeach;?>
</div>
<?if(empty($arParams['INNER'])):?>
<?=$arResult['NAV_STRING']?>
<?endif?>

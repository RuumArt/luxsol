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
 * @var string $templateFolder
 */

$this->setFrameMode(true);
?>
<? //pre($arResult)
				$quare = false;
				$db_old_groups = CIBlockElement::GetElementGroups($arResult['ID'], true);
				
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
?>
 	<div class="card">
 		<div class="container">
            
		 <div class="card__photos">
				<?if(!empty($arResult['OFFERS'])):?>
					<?foreach ($arResult['OFFERS'] as $k => $offer):?>
						<?if(!empty($offer['PICTURE'])):?>
						<div class="card-left slide<?=$offer['ID']?>" <?if($k !== 0):?>style="display: none;"<?endif?>>
							<div class="p-single-slider swiper-container">
								<div class="swiper-wrapper">
									<?if(!empty($offer['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
									<div class="p-single-slider__item swiper-slide">
										<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
									<?endif?>
									<?if(!empty($arResult['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
										<div class="p-single-slider__item swiper-slide">
											<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
										</div>
									<?endif?>
									<?foreach ($offer['PICTURE'] as $photo):?>
									<?
										$file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($photo, array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
									<div class="p-single-slider__item swiper-slide">
										<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
									<?endforeach;?>
								</div>
							</div>
							<? if(!empty($offer['PICTURE'])): ?>
							<div class="p-single-slider-thumb swiper-container">
								<div class="swiper-wrapper">
									<?if(!empty($offer['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
									<?endif?>
									<?foreach ($offer['PICTURE'] as $photo):?>
									<?$file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);?>
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
									<?endforeach;?>
								</div>
							</div>
							<? endif;?>
						</div>
						<?else:?>
						<div class="card-left slide<?=$offer['ID']?>" <?if($k !== 0):?>style="display: none;"<?endif?>>
							<div class="p-single-slider swiper-container">
									<div class="swiper-wrapper">
									<?if(!empty($offer['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
									<div class="p-single-slider__item swiper-slide">
										<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
									<?endif?>

									<?if(!empty($arResult['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
										<div class="p-single-slider__item swiper-slide">
											<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
										</div>
									<?endif?>
									
									<?foreach ($arResult['PROPERTIES']['PHOTO']['VALUE'] as $photo):?>
									<?
										$file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($photo, array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
									
									<div class="p-single-slider__item swiper-slide">
									<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
									<?endforeach;?>
								</div>
							</div>
							<? if(!empty($arResult['PROPERTIES']['PHOTO']['VALUE'])): ?>
							<div class="p-single-slider-thumb swiper-container">
									<div class="swiper-wrapper">
									<?if(!empty($offer['PREVIEW_PICTURE'])):?>
									<?$file = CFile::ResizeImageGet($offer['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);?>
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
									<?endif?>

									<?if(!empty($arResult['PREVIEW_PICTURE'])):?>
									<?
										$file = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
										$file_big = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									?>
										<div class="p-single-slider-thumb__item swiper-slide">
											<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
										</div>
									<?endif?>
									
									<?foreach ($arResult['PROPERTIES']['PHOTO']['VALUE'] as $photo):?>
									<?$file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);?>
									
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
									<?endforeach;?>
								</div>
							</div>
							<? endif; ?>
						</div>
						
						<?endif?>
					<?endforeach;?>
					
				<?else:?>
					<div class="card-left">
						<div class="p-single-slider swiper-container">
							<div class="swiper-wrapper">
								<?if(!empty($arResult['PREVIEW_PICTURE'])):?>
								<?
									$file = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
									$file_big = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
								?>
									<div class="p-single-slider__item swiper-slide">
										<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
								<?endif?>
								
								<?foreach ($arResult['PROPERTIES']['PHOTO']['VALUE'] as $photo):?>
								<?
								  $file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
								  $file_big = CFile::ResizeImageGet($photo, array('width'=>1200, 'height'=>1000), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);
								?>
									<div class="p-single-slider__item swiper-slide">
										<a href="<?=$file_big['src']?>" data-fancybox="product-<?=$arResult['ID']?>"><img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>"></a>
									</div>
								<?endforeach;?>
							</div>
						</div>
						<? if(!empty($arResult['PROPERTIES']['PHOTO']['VALUE'])): ?>
						<div class="p-single-slider-thumb swiper-container">
							<div class="swiper-wrapper">
								<?if(!empty($arResult['PREVIEW_PICTURE'])):?>
								<?$file = CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);?>
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
								<?endif?>
								
								<?foreach ($arResult['PROPERTIES']['PHOTO']['VALUE'] as $photo):?>
								<?$file = CFile::ResizeImageGet($photo, array('width'=>650, 'height'=>550), BX_RESIZE_IMAGE_PROPORTIONAL, true, false, false, 80);?>
									<div class="p-single-slider-thumb__item swiper-slide">
										<img src="<?=$file['src']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($arResult, 'DETAIL'))?>">
									</div>
								<?endforeach;?>
							</div>
						</div>
						<? endif; ?>
					</div>
				<?endif?>

				<?
					$comp = '';
					foreach($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'] as $id){
					   if($arResult['ID'] == $id['ID']){
					      $comp = 1;
					   }
					}
				
					$yes = $APPLICATION->get_cookie("favorits");
					$mas = (array) json_decode($yes,true);
				?>

				<div class="card__actions product-card__actions product-card__actions--mobile">
					<a href="#" class="product-card__like <?= (!in_array($arResult['ID'], $mas)) ? '' : '_active' ?>" data-id="<?=$arResult['ID']?>" title="Избранное">
						<svg width="28" height="25" viewBox="0 0 28 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        					<path d="M13.982 5.26197C12.8338 3.13098 10.0782 1 7.70527 1C1.42856 1 -1.78634 9.52397 4.10764 14.6677L14.2116 24L24.0859 14.6677C29.7503 9.23004 26.4588 1 20.1821 1C17.8092 1 15.0536 3.13098 13.982 5.26197Z" stroke="white" stroke-width="2" stroke-miterlimit="22.9256" stroke-linecap="round" stroke-linejoin="round"></path>
    					</svg>
					</a>
					<a href="#" class="product-card__compare <?= ($comp !== 1) ? '' : '_active' ?>" data-id="<?=$arResult['ID']?>" title="Сравнение">
						<svg xmlns="http://www.w3.org/2000/svg" width="98" height="98" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="3" stroke-linecap="butt" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
					</a>
				</div>
				
			</div>
				
			
			
			<div class="card-info">
				<div class="card-info__main">
					<div class="card-title">
						<h1 class="title"><?=$arResult['NAME']?></h1>
					</div>
					
					<? if(!empty($arResult['PROPERTIES']['PROD_ACCESS']['VALUE'])): ?>
						<div class="card-info__avilable">
							<?=$arResult['PROPERTIES']['PROD_ACCESS']['VALUE']?>
						</div>
					<? endif; ?>

					<div class="top-chars">


					<?if(!empty($arResult['PROPERTIES']['ARTICLE']['VALUE'])):?>
					<p><span>Číslo výrobku (SKU):</span> <span itemprop="sku" id="sku" class="sku-value"><?=$arResult['PROPERTIES']['ARTICLE']['VALUE']?></span></p>
					<?elseif(!empty($arResult['OFFERS'][0])):?>
					<p><span>Číslo výrobku (SKU):</span> <span itemprop="sku" id="sku" class="sku-value"><?=$arResult['OFFERS'][0]['ARTICLE']?></span></p>
					<?endif?>

					<?if(!empty($arResult['PROPERTIES']['RAZMER']['VALUE'])):?>
					<p><span>Veľkosť oka:</span> <span><?=$arResult['PROPERTIES']['RAZMER']['VALUE']?> mm</span></p>
					<?endif?>
					<?if(!empty($arResult['PROPERTIES']['MATERIAL']['VALUE'])):?>
					<p><span>Materiál:</span> <span><?=$arResult['PROPERTIES']['MATERIAL']['VALUE']?></span></p>
					<?endif?>
					
					<?if(!empty($arResult['PROPERTIES']['DIAMETR']['VALUE'])):?>
					<p><span>Hrúbka:</span> <span><?=$arResult['PROPERTIES']['DIAMETR']['VALUE']?> mm</span></p>
					<?endif?>

					<? 
						if(!empty($arResult['PROPERTIES']['COLOR']['VALUE']) && empty($arResult['OFFERS'])):
						$res = CIBlockElement::GetByID($arResult['PROPERTIES']['COLOR']['VALUE']);
						if($ar_res = $res->GetNext()) {
							$color_name = $ar_res['NAME'];
							$color_code = $ar_res['CODE'];
						}
					?>
						<p><span>Farba:</span> <span><?=$color_name?></span></p>
					<?endif?>

					<? if(!empty($arResult['OFFERS'])):?>
						<p><span>Farba:</span> <span class="colornew"></span></p>
					<?endif?>

					</div>

					<?//pre($arResult)?>
					<? /*if(!empty($arResult['PROPERTIES']['PARA']['VALUE'])):?>
					<p>Единица измерения: Пара</p>
					<?endif*/?>
					<? //pre($arResult)?>
					

					
					<? if(!empty($arResult['OFFERS'])):?>
				
                    
					<div class="card-info__cost">
						 Cena bez DPH:
						<span class="price_offer price_offer_dph_no"><input type="hidden" name="price-novat" id="price-item-novat" value="<?=$arResult['OFFERS'][0]['MIN_PRICE']['DISCOUNT_VALUE_NOVAT']?>" /><?=number_format($arResult['OFFERS'][0]['MIN_PRICE']['DISCOUNT_VALUE_NOVAT'], 2, ',', ' ');?> &#8364;</span> 
						<?if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):?>
						<span>/ m2</span>
						<?else:?>
						<span>/ <?=$arResult['OFFERS'][0]['CATALOG_MEASURE_NAME']?></span>
						<?endif?>
					</div>                    
                    
                    <div class="card-info__cost">
						 Cena s DPH:
						<span class="price_offer price_offer_dph"><input type="hidden" id="price-item" name="price" value="<?=$arResult['OFFERS'][0]['MIN_PRICE']['DISCOUNT_VALUE']?>" /><?=number_format($arResult['OFFERS'][0]['MIN_PRICE']['DISCOUNT_VALUE'], 2, ',', ' ');?> &#8364;</span> 
						<?if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):?>
						<span>/ m2</span>
						<?else:?>
						<span>/ <?=$arResult['OFFERS'][0]['CATALOG_MEASURE_NAME']?></span>
						<?endif?>
					</div>
                    
					<? else:?>
                    
					<div class="card-info__cost">
						Cena bez DPH:
						<span><input type="hidden" name="price" id="price-item-novat" value="<?=$arResult['MIN_PRICE']['DISCOUNT_VALUE_NOVAT']?>" /><?=number_format($arResult['MIN_PRICE']['DISCOUNT_VALUE_NOVAT'], 2, ',', ' ');?> &#8364; 
						<?if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):?>
						/ m2
						<?else:?>
						/ <?=$arResult['CATALOG_MEASURE_NAME']?>
						<?endif?>
						</span>
					</div>
                    
					<div class="card-info__cost">
						Cena s DPH:
						<span><input type="hidden" name="price" id="price-item" value="<?=$arResult['MIN_PRICE']['DISCOUNT_VALUE']?>" /><?=number_format($arResult['MIN_PRICE']['DISCOUNT_VALUE'], 2, ',', ' ');?> &#8364; 
						<?if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):?>
						/ m2
						<?else:?>
						/ <?=$arResult['CATALOG_MEASURE_NAME']?>
						<?endif?>
						</span>
					</div>
                    
					<?endif?>
				</div>

				<? if($arResult['PROPERTIES']['PROD_ACCESS']['VALUE_ENUM_ID'] != 20): ?>

				<?if(!empty($arResult['OFFERS'])):?>
					<div class="card-row">
						<h5 class="card-row__title">Farba</h5>
						<ul class="card-color">
                        
						<? foreach ($arResult['OFFERS'] as $offer):?>
							<li onclick="$('#sku').text('<?=$offer['ARTICUL']?>');$('.card-color__item').removeClass('_active');$(this).children('a').addClass('_active');$('#colorcheck').val('<?=$offer['COLOR_NAME']?>');$('.colornew').html('<?=$offer['COLOR_NAME']?>');slideoffer('<?=$offer['ID']?>'); price('<?=$offer['ID']?>'); cart2('<?=$offer['ID']?>'); getFromCart('<?=$offer['ID']?>'); ">
                            	<a href="javascript:void(0)" alt="<?=$offer['COLOR_NAME']?>" title="<?=$offer['COLOR_NAME']?>" class="card-color__item" style="background-color: <?=$offer['COLOR']?>"></a>
							</li>
						<? endforeach;?>

						<input type="text" name="colorcheck" value="" id="colorcheck" style="display: none"/>
						</ul>
					</div>
				<? else:?>
					<?if(!empty($arResult['PROPERTIES']['COLOR']['VALUE'])):?>
					<div class="card-row">
						<h5 class="card-row__title">Farba</h5>
						<ul class="card-color">
							<li><div class="card-color__item" style="background-color: <?=$color_code?>"></div></li>
						</ul>
						<input type="text" name="colorcheck" value="<?=$color_name?>" id="colorcheck" style="display: none"/>
					</div>
					<? endif; ?>
				<?endif?>
				<?
				
				if(in_array(1, $ar_new_groups) || in_array(46, $ar_new_groups)):
						$quare = true;
						?>
						<?if($arResult['PROPERTIES']['ARTICLE']['VALUE'] == 1015
						|| $arResult['PROPERTIES']['ARTICLE']['VALUE'] == 1024
						|| $arResult['PROPERTIES']['ARTICLE']['VALUE'] == 1041
						|| $arResult['PROPERTIES']['ARTICLE']['VALUE'] == 1016):?>
						<br>
<? /*						<div class="product-note">
							<p>Excl. Tax: 0,92 € Incl. Tax: 1,10 €
Upozornenie:   Ceny uvedené na stránke za 1m2 sú platné pre siete s plochou nad 20 m2. Pri menších plochách platia prirážky: 10-19,99 m2 cena + 20%, 5-9,99 m2 cena + 35%, 0-4,99m2 cena + 100 %. Ceny sú automaticky prepočítané.</p>						
						</div><br> */ ?>
						<?endif?>
						<br>
						<div class="product-note">
                        
                        <? 
							$res = CIBlockElement::GetByID(508);
								if($arEl = $res->GetNext())
									  $tax = strip_tags($arEl['PREVIEW_TEXT'], '<p>');
						?>
                        
                        
							<p><?=$tax?></p>						
						</div>
						
						<!-- <div style="display: inline-block">
							<h5 class="card-row__title">Výška (m)</h5>
							<div class="product__count">
								<input type="text" class="heightsetka" name="height" value="" style="width: 85px;">
							</div>
						</div>
						<div style="display: inline-block">
							<h5 class="card-row__title">Šírka (m)</h5>
							<div class="product__count">
								<input type="text" class="widthsetka" name="width" value="" style="width: 85px;">
							</div>	
						</div>
						<div>
						<p style="display: none;color: red;margin-top:10px;" id="errorsetka"><?=Loc::getMessage('ROOM_SIZE_ERROR')?></p>
						</div> -->
						
						<div class="card__squere">
							<p style="display: none;color: red;margin-top:10px;" id="errorsetka"><?=Loc::getMessage('ROOM_SIZE_ERROR')?></p>

							<div class="sizes">
								<div class="sizes__item">
									<?=str_replace('{{INDEX}}', '0', room_sizes_item_inner('width: 85px;'))?>
									<?=room_sizes_item_plus()?>
								</div>
							</div>

							<? // Заготовки для JS: из них all.js собирает новую строку по "+"
							room_sizes_item_templates('width: 85px;'); ?>
                    	</div>
						

				<?endif?>
				<div class="card-row">
					<? if(!in_array(1, $ar_new_groups)): ?>
						<h5 class="card-row__title"><?=Loc::getMessage('ROOM_SIZE_COUNT')?></h5>
						<div class="product__count product__count__item">
							<input type="text" value="1" readonly class="quant-item">
						</div>
					<? endif; ?>
					<ul class="card-options">	
									<? $yes = $APPLICATION->get_cookie("favorits");
									$mas = (array) json_decode($yes,true);		
									if(!in_array($arResult['ID'], $mas)):?>
										<li>
										<a style="display: inline-block;" href="" class="hided<?=$arResult['ID']?> btn" onclick="add_to_fav(<?=$arResult['ID']?>);$(this).hide();$('.favor<?=$arResult['ID']?>').show();return false;">
											<img src="<?=SITE_TEMPLATE_PATH?>/images/favorite.png" alt="">
											
										</a>
										</li>
										<li>
										<a style="display: none;" onclick="add_to_fav_delete(<?=$arResult['ID']?>);$(this).hide();$('.hided<?=$arResult['ID']?>').show();return false;" href="" class="favor<?=$arResult['ID']?> btn">
											<img src="<?=SITE_TEMPLATE_PATH?>/images/favorite.png" alt="">
											
										</a>
										</li>
		                            <?else:?>
		                            	<li>
		                            	<a style="display: none;" href="" class="hided<?=$arResult['ID']?> btn" onclick="add_to_fav(<?=$arResult['ID']?>);$(this).hide();$('.favor<?=$arResult['ID']?>').show();return false;">
											<img src="<?=SITE_TEMPLATE_PATH?>/images/favorite.png" alt="">
											
										</a>
										</li>
										<li>
										<a style="display: inline-block;" href="" onclick="add_to_fav_delete(<?=$arResult['ID']?>);$(this).hide();$('.hided<?=$arResult['ID']?>').show();return false;" class="favor<?=$arResult['ID']?> btn">
											<img src="<?=SITE_TEMPLATE_PATH?>/images/favorite.png" alt="">
											
										</a>
										</li>
		                            <?endif?>
		                            
		                            <?
		                            $comp = '';
		                            foreach($_SESSION['CATALOG_COMPARE_LIST'][1]['ITEMS'] as $id){
		                               if($arResult['ID'] == $id['ID']){
		                                  $comp = 1;
		                               }
		                            }?>
		                            <?if($comp !== 1):?>
		                            	<li>
			                            	<a href="" onclick="add_to_comp('<?=$arResult['ID']?>');$(this).hide();$('.compar<?=$arResult['ID']?>').show();return false;" class="comrarno<?=$arResult['ID']?> btn">
												<img src="<?=SITE_TEMPLATE_PATH?>/images/graphic.png" alt="">
											
											</a>
										</li>
										<li>
											<a style="display: none" href="" class="compar<?=$arResult['ID']?> btn" onclick="delete_to_comp('<?=$arResult['ID']?>');$(this).hide();$('.comrarno<?=$arResult['ID']?>').show();return false;">
												<img src="<?=SITE_TEMPLATE_PATH?>/images/graphic.png" alt="">
												<span>V porovnaní</span>
											</a>
										</li>
		                            <?else:?>
		                            	<li>
			                            	<a href="" style="display: none" onclick="add_to_comp('<?=$arResult['ID']?>');$(this).hide();$('.comrar<?=$arResult['ID']?>').show();return false;" class="comrarno<?=$arResult['ID']?> btn">
												<img src="<?=SITE_TEMPLATE_PATH?>/images/graphic.png" alt="">
												
											</a>
										</li>
										<li>
											<a href="" class="comrar<?=$arResult['ID']?> btn" onclick="delete_to_comp('<?=$arResult['ID']?>');$(this).hide();$('.comrarno<?=$arResult['ID']?>').show();return false;">
												<img src="<?=SITE_TEMPLATE_PATH?>/images/graphic.png" alt="">
												
											</a>
										</li>
									<?endif?> 
					</ul>			
				</div>
				
				<?if($quare == true):?>
					<?//pRE($_SESSION['PROP'])?>
					<?if(!empty($arResult['OFFERS'])):?>
					
						<?foreach ($arResult['OFFERS'] as $k => $offer):?>
						<div class="card-row btn-row btncartnull btncart<?=$offer['ID']?>" <?if($k !== 0):?>style="display: none;"<?endif?>>
							<a href="" class="btn btn-success" onclick="addcartq('<?=$offer['ID']?>'); return false;">Do košíka</a>
							
						</div>
						<?endforeach;?>
					<?else:?>
						<div class="card-row btn-row ">
							<a href="" class="btn btn-success" onclick="addcartq('<?=$arResult['ID']?>');return false;">Do košíka</a>
							
						</div>
					<?endif?>
				<?else:?>
				
					<?if(!empty($arResult['OFFERS'])):?>
					
						<?foreach ($arResult['OFFERS'] as $k => $offer):?>
						<div class="card-row btn-row btncartnull btncart<?=$offer['ID']?>" <?if($k !== 0):?>style="display: none;"<?endif?>>
							<a href="#" data-target="cart" class="btn btn-success" onclick="addcart('<?=$offer['ID']?>'); return false;">Do košíka</a>
							
						</div>
						<?endforeach;?>
					<?else:?>
						<div class="card-row btn-row ">
							<a href="#" class="btn btn-success"  data-target="cart" onclick="addcart('<?=$arResult['ID']?>');return false;">Do košíka</a>
							
						</div>
					<?endif?>
				<?endif?>

				
				<? else: ?>
					<p class="product_not">Dočasne nedostupné</p>

				<? endif; ?>
								
			</div>
 		</div>
 	</div>

	<?
	// Разметка товара для Google: цена и наличие те же, что на карточке выше
	$roomProductSchema = room_product_schema(
		$arResult,
		in_array(1, (array)$ar_new_groups) || in_array(46, (array)$ar_new_groups)
	);
	if ($roomProductSchema):
	?>
	<script type="application/ld+json"><?=\Bitrix\Main\Web\Json::encode($roomProductSchema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?></script>
	<? endif; ?>

	 <?
        global $arrFilterU;
        if(!empty($arResult['PROPERTIES']['ITEM_UPSELL']['VALUE'])):
			
            $arrFilterU = array("ID" => $arResult['PROPERTIES']['ITEM_UPSELL']['VALUE']);
            $section_id = "";
    ?>
	
	<?$APPLICATION->IncludeComponent(
		"bitrix:catalog.section", 
		"upsell", 
		array(
			"COMPONENT_TEMPLATE" => "upsell",
			"IBLOCK_TYPE" => "luxol",
			"IBLOCK_ID" => "1",
			"SECTION_ID" => $section_id,
			"SECTION_CODE" => "",
	        "UPSELL_TITLES" => $arResult['PROPERTIES']['UPSELL_TITLES']['VALUE'],
	        // Порядок товаров, как их перечислил менеджер: по нему
	        // result_modifier выстроит карточки, чтобы подписи
	        // из UPSELL_TITLES встали к своим товарам
	        "UPSELL_IDS" => $arResult['PROPERTIES']['ITEM_UPSELL']['VALUE'],
			"SECTION_USER_FIELDS" => array(
				0 => "",
				1 => "",
			),
			"FILTER_NAME" => "arrFilterU",
			"INCLUDE_SUBSECTIONS" => "Y",
			"SHOW_ALL_WO_SECTION" => "Y",
			"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
			"HIDE_NOT_AVAILABLE" => "N",
			"HIDE_NOT_AVAILABLE_OFFERS" => "N",
			"ELEMENT_SORT_FIELD2" => "id",
			"ELEMENT_SORT_ORDER2" => "desc",
			"PAGE_ELEMENT_COUNT" => 8,
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
			"CACHE_TYPE" => "N",
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
			"DISABLE_INIT_JS_IN_COMPONENT" => "N",
			"INNER" => "Y",
			"OFFERS_SORT_FIELD" => "sort",
			"OFFERS_SORT_ORDER" => "asc",
			"OFFERS_SORT_FIELD2" => "id",
			"OFFERS_SORT_ORDER2" => "desc",
			"OFFERS_FIELD_CODE" => array(
				0 => "",
				1 => "",
			)
		),
		false
	);?>

	<? endif; ?>
	
	<? $view_tabs = \COption::GetOptionString( "askaron.settings", "UF_VIEW_TABS"); ?>
 	
 	<div class="card-tabs tabs">
		<div class="container">
			<div class="track-nav">
			<ul class="tab-nav">
				<? if(in_array(1, $view_tabs)): ?>
				<li>
					<a href="" class="tab-link <?= ($view_tabs[0] == 1) ? 'active' : '' ?>" data-tab="tab-2">Technické parametre</a>
				</li>
				<? endif; ?>
				<? if(in_array(2, $view_tabs)): ?>
				<li>
					<a href="" class="tab-link <?= ($view_tabs[0] == 2) ? 'active' : '' ?>" data-tab="tab-1">Popis</a>
				</li>
				<? endif; ?>
				<? if(in_array(3, $view_tabs)): ?>
				<li>
					<a href="" class="tab-link <?= ($view_tabs[0] == 3) ? 'active' : '' ?>" data-tab="tab-3">Platba</a>
				</li>
				<? endif; ?>
				<?/*<li>
					<a href="" class="tab-link" data-tab="tab-4">Recenzia</a>
				</li>
				<li>
					<a href="" class="tab-link" data-tab="tab-5">Otázky a odpovede</a>
				</li> 	*/?>		 			 			 			
			</ul>
			</div>
			<div class="tabs-content">
				<? if(in_array(2, $view_tabs)): ?>
				<div class="tab tab-1 text-block <?= ($view_tabs[0] == 2) ? 'active' : '' ?>">
					<?=$arResult['~PREVIEW_TEXT']?>
				</div>
				<? endif; ?>
				<? if(in_array(1, $view_tabs)): ?>
				<div class="tab tab-2 text-block <?= ($view_tabs[0] == 1) ? 'active' : '' ?>">
					<?=$arResult['~DETAIL_TEXT']?>
				</div>
				<? endif; ?>
				<? if(in_array(3, $view_tabs)): ?>
				<div class="tab tab-3 text-block <?= ($view_tabs[0] == 3) ? 'active' : '' ?>">
					<?
					$arFilter = array("IBLOCK_ID" => 8, "ACTIVE" => "Y", "ID" => 508);
					$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("PREVIEW_TEXT"));
					while($ar_fields = $res->GetNext())
					{
					 	$TEXT = $ar_fields['PREVIEW_TEXT'];
					}
					?>
					<?=$TEXT?>
				</div>
				<? endif; ?>	
			</div>
 		</div>
 	</div>
 	
 	<?
	// Порядок фиксированный: раньше стояла сортировка rand, и блок при каждой
	// перезагрузке показывал разные товары. Сортировка по ID делает подборку
	// постоянной, а какие именно товары показывать - решаем ниже
	$by = 'id';
	$sort = 'asc';
	$count = 3;

	global $arrFilterG;

	$relatedIds = !empty($arResult['PROPERTIES']['ITEM_WITH']['VALUE'])
		// Связи заданы вручную - берём их, отсеивая отключённые товары
		? room_active_ids($arResult['PROPERTIES']['ITEM_WITH']['VALUE'], (int)$arResult['IBLOCK_ID'])
		// Связей нет - соседи по разделу, одни и те же при каждом заходе
		: room_related_ids($arResult, $count);

	$arrFilterG = array("ID" => $relatedIds);
	$section_id = "";

	// Показывать нечего: заголовок над пустым блоком не рисуем
	if (!empty($relatedIds)):
	?>
 	<div class="other-product">
 		<div class="container">
			<div class="section-header">
				<h2 class="section-header__title">SÚVISIACE PRODUKTY</h2>
			</div> 
				<? $APPLICATION->IncludeComponent(
							"bitrix:catalog.section", 
							"catalog", 
							array(
								"COMPONENT_TEMPLATE" => "catalog",
								"IBLOCK_TYPE" => "luxol",
								"IBLOCK_ID" => "1",
								"SECTION_ID" => $section_id,
								"SECTION_CODE" => "",
								"SECTION_USER_FIELDS" => array(
									0 => "",
									1 => "",
								),
								"FILTER_NAME" => "arrFilterG",
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
								"CACHE_TYPE" => "N",
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
								"DISABLE_INIT_JS_IN_COMPONENT" => "N",
								"INNER" => "Y"
							),
							false
						);?>	
 		</div>
 	</div>
	<? endif; ?>

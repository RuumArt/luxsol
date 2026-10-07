<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
foreach($arResult["ITEMS"] as $key=>$arItem)
{
	if($key == 14){
		foreach ($arItem['VALUES'] as $k => $item){
			if($k == '6х6'){
				$size_new[0] = $item;
			}
			elseif($k == '10х10'){
				$size_new[1] = $item;
			}
			elseif($k == '10*10'){
				$size_new[2] = $item;
			}
			elseif($k == '20*20'){
				$size_new[3] = $item;
			}
			elseif($k == '20х20'){
				$size_new[4] = $item;
			}
			elseif($k == '30х30'){
				$size_new[5] = $item;
			}
			elseif($k == '35х35'){
				$size_new[6] = $item;
			}
			elseif($k == '40х40'){
				$size_new[7] = $item;
			}
			elseif($k == '50х50'){
				$size_new[8] = $item;
			}
			elseif($k == '70х70'){
				$size_new[9] = $item;
			}
			elseif($k == '100х100'){
				$size_new[10] = $item;
			}
			elseif($k == '100х100'){
				$size_new[] = $item;
			}
		}
		ksort($size_new);
		$arResult["ITEMS"][14]['VALUES'] = $size_new;
	}

	if($key == 28) {
		$values = [];

		foreach ($arItem['VALUES'] as $k => $item){
			$values[] = $item;
		};

		krsort($values, SORT_STRING);

		$arResult["ITEMS"][28]['VALUES'] = $values;
	}
}

$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/colors.css',
	'TEMPLATE_CLASS' => 'bx-'.$arParams['TEMPLATE_THEME']
);

if (isset($templateData['TEMPLATE_THEME']))
{
	$this->addExternalCss($templateData['TEMPLATE_THEME']);
}

?>
<form name="<?echo $arResult["FILTER_NAME"]."_form"?>" action="<?echo $arResult["FORM_ACTION"]?>" method="get" class="sidebar">
			<button type="button" class="sidebar-close"><span></span><span></span></button>
			<?foreach($arResult["HIDDEN"] as $arItem):?>
			<input type="hidden" name="<?echo $arItem["CONTROL_NAME"]?>" id="<?echo $arItem["CONTROL_ID"]?>" value="<?echo $arItem["HTML_VALUE"]?>" />
			<?endforeach;?>
			
				<?/*foreach($arResult["ITEMS"] as $key=>$arItem)//prices
				{
					$key = $arItem["ENCODED_ID"];
					if(isset($arItem["PRICE"])):
						if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
							continue;

						$step_num = 4;
						$step = ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"]) / $step_num;
						$prices = array();
						if (Bitrix\Main\Loader::includeModule("currency"))
						{
							for ($i = 0; $i < $step_num; $i++)
							{
								$prices[$i] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MIN"]["VALUE"] + $step*$i, $arItem["VALUES"]["MIN"]["CURRENCY"], false);
							}
							$prices[$step_num] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MAX"]["VALUE"], $arItem["VALUES"]["MAX"]["CURRENCY"], false);
						}
						else
						{
							$precision = $arItem["DECIMALS"]? $arItem["DECIMALS"]: 0;
							for ($i = 0; $i < $step_num; $i++)
							{
								$prices[$i] = number_format($arItem["VALUES"]["MIN"]["VALUE"] + $step*$i, $precision, ".", "");
							}
							$prices[$step_num] = number_format($arItem["VALUES"]["MAX"]["VALUE"], $precision, ".", "");
						}
						?>
						<div class="<?if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"):?>col-sm-6 col-md-4<?else:?>col-lg-12<?endif?> bx-filter-parameters-box bx-active">
							<span class="bx-filter-container-modef"></span>
							<div class="bx-filter-parameters-box-title" onclick="smartFilter.hideFilterProps(this)"><span><?=$arItem["NAME"]?></span><i data-role="prop_angle" class="fa fa-angle-<?if ($arItem["DISPLAY_EXPANDED"]== "Y"):?>up<?else:?>down<?endif?>"></i></div>
							<div class="bx-filter-block" data-role="bx_filter_block">
								<div class="row bx-filter-parameters-box-container">
									<div class="col-xs-6 bx-filter-parameters-box-container-block bx-left">
										<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_FROM")?></i>
										<div class="bx-filter-input-container">
											<input
												class="min-price"
												type="text"
												name="<?echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"]?>"
												id="<?echo $arItem["VALUES"]["MIN"]["CONTROL_ID"]?>"
												value="<?echo $arItem["VALUES"]["MIN"]["HTML_VALUE"]?>"
												size="5"
												onkeyup="smartFilter.keyup(this)"
											/>
										</div>
									</div>
									<div class="col-xs-6 bx-filter-parameters-box-container-block bx-right">
										<i class="bx-ft-sub"><?=GetMessage("CT_BCSF_FILTER_TO")?></i>
										<div class="bx-filter-input-container">
											<input
												class="max-price"
												type="text"
												name="<?echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"]?>"
												id="<?echo $arItem["VALUES"]["MAX"]["CONTROL_ID"]?>"
												value="<?echo $arItem["VALUES"]["MAX"]["HTML_VALUE"]?>"
												size="5"
												onkeyup="smartFilter.keyup(this)"
											/>
										</div>
									</div>

									<div class="col-xs-10 col-xs-offset-1 bx-ui-slider-track-container">
										<div class="bx-ui-slider-track" id="drag_track_<?=$key?>">
											<?for($i = 0; $i <= $step_num; $i++):?>
											<div class="bx-ui-slider-part p<?=$i+1?>"><span><?=$prices[$i]?></span></div>
											<?endfor;?>

											<div class="bx-ui-slider-pricebar-vd" style="left: 0;right: 0;" id="colorUnavailableActive_<?=$key?>"></div>
											<div class="bx-ui-slider-pricebar-vn" style="left: 0;right: 0;" id="colorAvailableInactive_<?=$key?>"></div>
											<div class="bx-ui-slider-pricebar-v"  style="left: 0;right: 0;" id="colorAvailableActive_<?=$key?>"></div>
											<div class="bx-ui-slider-range" id="drag_tracker_<?=$key?>"  style="left: 0%; right: 0%;">
												<a class="bx-ui-slider-handle left"  style="left:0;" href="javascript:void(0)" id="left_slider_<?=$key?>"></a>
												<a class="bx-ui-slider-handle right" style="right:0;" href="javascript:void(0)" id="right_slider_<?=$key?>"></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?
						$arJsParams = array(
							"leftSlider" => 'left_slider_'.$key,
							"rightSlider" => 'right_slider_'.$key,
							"tracker" => "drag_tracker_".$key,
							"trackerWrap" => "drag_track_".$key,
							"minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
							"maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
							"minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
							"maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
							"curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
							"curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
							"fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"] ,
							"fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
							"precision" => $precision,
							"colorUnavailableActive" => 'colorUnavailableActive_'.$key,
							"colorAvailableActive" => 'colorAvailableActive_'.$key,
							"colorAvailableInactive" => 'colorAvailableInactive_'.$key,
						);
						?>
						<script type="text/javascript">
							BX.ready(function(){
								window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
							});
						</script>
					<?endif;
				}*/

				//not prices
				foreach($arResult["ITEMS"] as $key=>$arItem)
				{
					if(
						empty($arItem["VALUES"])
						|| isset($arItem["PRICE"])
					)
						continue;

					if (
						$arItem["DISPLAY_TYPE"] == "A"
						&& (
							$arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0
						)
					)
						continue;
					?>
					<div class="sidebar-item open">
 						<div class="sidebar-title">
							<div class="sidebar-title__target">
								<?=$arItem["NAME"]?>
							</div>
							<? if($arItem["NAME"] == 'Materiál'): ?>
								<div class="sidebar-item__tooltip tooltip">
									<div class="tooltip__icon"></div>
									<div class="tooltip__text">
										<p>Polyamidové vlákno (PA) má jedinečné vlastnosti – jeho odolnosť voči oderu a ohybu prevyšuje akékoľvek iné chemické a prírodné materiály. Polyamidové siete sú vhodné do interiéru, ale aj exteriéru.</p>
										<p>Polypropylénové vlákno (PP) je charakteristické svojou mechanickou pevnosťou, odolnosťou a nízkou cenou. Polypropylénová sieť má v porovnaní s polyamidovou strednú životnosť a vyžaduje údržbu (má strednú odolnosť voči UV žiareniu a oderu). Tento materiál má dlhšiu životnosť v interiéri.</p>
									</div>
								</div>
							<? endif; ?>
							
							<? /* if($arItem["NAME"] == 'Farba'): ?>
								<div class="sidebar-item__tooltip tooltip">
									<div class="tooltip__icon"></div>
									<div class="tooltip__text">
										<p>Polypropylénové siete sa nedajú farbiť, pretože neabsorbujú vlhkosť. Majú teda rovnakú farbu ako niť, z ktorej je sieť utkaná.</p> 
									</div>
								</div>
							<? endif;*/ ?>
							
							<? if($arItem["NAME"] == 'Hrúbka'): ?>
								<div class="sidebar-item__tooltip tooltip">
									<div class="tooltip__icon"></div>
									<div class="tooltip__text">
										<p>Priemer nite má určujúcu úlohu pri výbere siete. Čím väčší priemer, tým dlhšia životnosť.</p> 
									</div>
								</div>
							<? endif; ?>

 						</div>

							<?
							$arCur = current($arItem["VALUES"]);
							switch ($arItem["DISPLAY_TYPE"])
							{
								case "A"://NUMBERS_WITH_SLIDER
									
									break;
								case "B"://NUMBERS
									
									break;
								case "G"://CHECKBOXES_WITH_PICTURES
									
									break;
								case "H"://CHECKBOXES_WITH_PICTURES_AND_LABELS
									
									break;
								case "P"://DROPDOWN
									$checkedItemExist = false;
									
									break;
								case "R"://DROPDOWN_WITH_PICTURES_AND_LABELS
									
									break;
								case "K"://RADIO_BUTTONS
									
									break;
								case "U"://CALENDAR
									
									break;
								default://CHECKBOXES
									?>
									<div class="sidebar-item__content">
										<?foreach($arItem["VALUES"] as $val => $ar):?>
											<div class="sidebar-item__checkbox">
											<div class="sidebar-item__checkbox-inner">
											<label class="checkbox">
				 								<input type="checkbox" 
				 								value="<? echo $ar["HTML_VALUE"] ?>"
															name="<? echo $ar["CONTROL_NAME"] ?>"
															id="<? echo $ar["CONTROL_ID"] ?>"
															<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
				 								hidden>
				 								<span class="checkbox-text"><?=$ar["VALUE"];?><?= ($arItem["NAME"] == 'Hrúbka' || $arItem["NAME"] == 'Veľkosť oka (mm)'  ? ' mm' : '') ?></span>
				 							</label>
											 	<? if($arItem["NAME"] == 'Размер ячейки'): ?>
											 	<div class="sidebar-item__tooltip tooltip">
													<div class="tooltip__icon"></div>
													<div class="tooltip__text">
														<? switch ($ar["VALUE"]) {
															case '6х6':
																echo '6x6mm je najmenší rozmer oka, je ideálny na oplotenie veľkých ihrísk, šitie vriec a prepravných sietí.';
																break;
															case '10х10':
																echo '10x10mm je jeden z najhustejších výpletov, ktorý sa používa na ozdobu stien v rôznych priestoroch, v liahniach a na zabezpečenie ochrany vtákov a zvierat.';
																break;
															case '20х20':
																echo 'Sieť s rozmerom oka 20x20mm je vhodná na oplotenie stolnotenisových kurtov alebo detských ihrísk. Tento rozmer sa tiež používa na výrobu trampolínových sietí a plotov na golfových ihriskách.';
																break;
															case '35х35':
																echo 'Sieť s okom 35x35mm je ideálna na oplotenie akýchkoľvek športovísk, no chráni pred pukom aj divákov na hokejovom štadióne.';
																break;
															case '40х40':
																echo '40x40mm je univerzálna veľkosť oka, vhodná na oplotenie ľubovoľného objektu, či už pozemku chaty alebo športového ihriska. Veľkosť ôk 40x40mm ochráni pred hokejovým pukom aj tenisovou loptičkou.';
																break;
															case '50х50':
																echo 'Rozmer oka 50x50mm je ideálny na ochranu rôznych športových zariadení (informačné tabule, sklá, aparatúry, stojany). Poslúži aj na oplotenie športovísk ako tenisový kurt, futbalové ihrisko a pod.';
																break;
															case '70х70':
																echo 'Rozmer oka 70x70mm je vynikajúcou voľbou, ak potrebujete veľké oko a oko s rozmerom 100x100mm je už priveľké. Takáto sieť je ideálna na oplotenie ihrísk, deliacich zón, ochranu okien a stropov v telocvičniach atď.';
																break;
															case '100х100':
																echo 'Rozmer oka 100x100mm je optimálny na oplotenie ihrísk, športových štadiónov a hracích plôch. Je skvelý aj na ochranu okien pred strelami v telocvičniach.';
																break;
														}
														?>
													</div>
												</div>
												<? endif; ?>
											</div>
											</div>
										<?endforeach;?>
									</div>
							<?
							}
							?>

							</div>
				<?
				}
				?>
				<button class="btn btn-success"
								id="set_filter"
								name="set_filter"
								value="<?=GetMessage("CT_BCSF_SET_FILTER")?>"
				>Filtrovať</button>
 				<button class="btn btn-cancel"
 								id="del_filter"
								name="del_filter"
								value="<?=GetMessage("CT_BCSF_DEL_FILTER")?>"
 				>Vymazať filtre</button>
		</form>
<!-- <script type="text/javascript">
	var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
</script> -->
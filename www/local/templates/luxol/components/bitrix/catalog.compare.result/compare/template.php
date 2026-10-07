<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

$isAjax = ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["ajax_action"]) && $_POST["ajax_action"] == "Y");

//pre($arResult["SHOW_PROPERTIES"])
?>
<div class="section-header">
				<h2 class="section-header__title">Porovnanie tovarov</h2>
			</div> 	
 
 			<ul class="compare-nav">
 				<li>
 					<a href="?DIFFERENT=N" class="btn btn-default <?if($_GET['DIFFERENT'] == 'N'):?>active<?endif?>">Všetky parametre</a>
 				</li>
 				<li>
 					<a href="?DIFFERENT=Y" class="btn btn-default <?if($_GET['DIFFERENT'] == 'Y'):?>active<?endif?>">Odlišnosti</a>
 				</li> 				
 			</ul>
 			<div class="compare-list">

		                            	<?foreach($arResult['ITEMS'] as $Item):?>
		                            		<div class="compare-item">
												<a href="?action=DELETE_FROM_COMPARE_RESULT&ID=<?=$Item['~ID']?>" class="compare-item__delete">
													<img src="<?=SITE_TEMPLATE_PATH?>/images/compare/close.png" alt="">
												</a>
												 <? 
			                                        	$arFilter = Array("IBLOCK_ID"=>1, "ID" => $Item['~ID']);
			                                        	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("PREVIEW_PICTURE"));
			                                        	while($ar_fields = $res->GetNext())
			                                        	{
			                                        		$arr = $ar_fields;
			                                        	}
			                                        	?>
			                                        	<?$file = CFile::GetFileArray($arr['PREVIEW_PICTURE'])?>
			                                        	<?$img1 = CFile::ResizeImageGet($file, array('width'=>321, 'height'=>300), BX_RESIZE_IMAGE_PROPORTIONAL, true);?>
			                                            
							 					<a href="<?=$Item['DETAIL_PAGE_URL']?>" class="compare-item__image" style="background-image: url(<?=$img1['src']?>)"></a>
							 					<a href="<?=$Item['DETAIL_PAGE_URL']?>" class="compare-item__title"><?=$Item['NAME']?></a>
							 					<div class="compare-item__cost"><?=number_format($Item['PRICE_MATRIX']['MATRIX'][1][0]['PRICE'], 0, ',', ' ')?>  €</div>
							 					<table class="compare-item__table" style="width: 100%;">
							 					<? 
							 					$elem = array();
													foreach ($arResult["SHOW_PROPERTIES"] as $code => $arProperty)
													{
														$showRow = true;
														if ($arResult['DIFFERENT'])
														{
															$arCompare = array();
															foreach($arResult["ITEMS"] as &$arElement)
															{
																$arPropertyValue = $arElement["DISPLAY_PROPERTIES"][$code]["VALUE"];
																if (is_array($arPropertyValue))
																{
																	sort($arPropertyValue);
																	$arPropertyValue = implode(" / ", $arPropertyValue);
																}
																$arCompare[] = $arPropertyValue;
															}
															unset($arElement);
															$showRow = (count(array_unique($arCompare)) > 1);
														}
													
														if ($showRow)
														{foreach($arResult["ITEMS"] as &$arElement)
															if($arElement['ID'] == $Item['ID']){
																	{ $el = (is_array($arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"])? implode("/ ", $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]): $arElement["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
																		$elem[$arElement['ID']][$arProperty["NAME"]] = $el;
						
																	}
																	unset($arElement);
															}
															}
														}
													?>				
												<?$i=0;?>
												<?foreach($elem as $key => $val):?>
													<?foreach($val as $k => $v):?>
														<tr>
														<td><?=$k?></td>
														<td style="width: 40%;"><?=$v?>&nbsp;</td>
														</tr>
													<?endforeach?>
		
												<?endforeach?>	
							 										 						
							 					</table>
							 				</div>

		                                <?endforeach?>
			</div>
 										
							

<script type="text/javascript">
	var CatalogCompareObj = new BX.Iblock.Catalog.CompareClass("bx_catalog_compare_block");
</script>
<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

$documentRoot = Main\Application::getDocumentRoot();

if (empty($arParams['TEMPLATE_THEME']))
{
	$arParams['TEMPLATE_THEME'] = Main\ModuleManager::isModuleInstalled('bitrix.eshop') ? 'site' : 'blue';
}

if ($arParams['TEMPLATE_THEME'] === 'site')
{
	$templateId = Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', $component->getSiteId());
	$templateId = preg_match('/^eshop_adapt/', $templateId) ? 'eshop_adapt' : $templateId;
	$arParams['TEMPLATE_THEME'] = Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', 'blue', $component->getSiteId());
}

if (!empty($arParams['TEMPLATE_THEME']))
{
	if (!is_file($documentRoot.'/bitrix/css/main/themes/'.$arParams['TEMPLATE_THEME'].'/style.css'))
	{
		$arParams['TEMPLATE_THEME'] = 'blue';
	}
}

if (!isset($arParams['DISPLAY_MODE']) || !in_array($arParams['DISPLAY_MODE'], array('extended', 'compact')))
{
	$arParams['DISPLAY_MODE'] = 'extended';
}

$arParams['USE_DYNAMIC_SCROLL'] = isset($arParams['USE_DYNAMIC_SCROLL']) && $arParams['USE_DYNAMIC_SCROLL'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) && $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';

$arParams['PRICE_DISPLAY_MODE'] = isset($arParams['PRICE_DISPLAY_MODE']) && $arParams['PRICE_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['TOTAL_BLOCK_DISPLAY']) || !is_array($arParams['TOTAL_BLOCK_DISPLAY']))
{
	$arParams['TOTAL_BLOCK_DISPLAY'] = array('top');
}

if (empty($arParams['PRODUCT_BLOCKS_ORDER']))
{
	$arParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,columns';
}

if (is_string($arParams['PRODUCT_BLOCKS_ORDER']))
{
	$arParams['PRODUCT_BLOCKS_ORDER'] = explode(',', $arParams['PRODUCT_BLOCKS_ORDER']);
}

$arParams['USE_PRICE_ANIMATION'] = isset($arParams['USE_PRICE_ANIMATION']) && $arParams['USE_PRICE_ANIMATION'] === 'N' ? 'N' : 'Y';
$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

if ($arParams['USE_GIFTS'] === 'Y')
{
	$arParams['GIFTS_BLOCK_TITLE'] = isset($arParams['GIFTS_BLOCK_TITLE']) ? trim((string)$arParams['GIFTS_BLOCK_TITLE']) : Loc::getMessage('SBB_GIFTS_BLOCK_TITLE');

	CBitrixComponent::includeComponentClass('bitrix:sale.products.gift.basket');

	$giftParameters = array(
		'SHOW_PRICE_COUNT' => 1,
		'PRODUCT_SUBSCRIPTION' => 'N',
		'PRODUCT_ID_VARIABLE' => 'id',
		'USE_PRODUCT_QUANTITY' => 'N',
		'ACTION_VARIABLE' => 'actionGift',
		'ADD_PROPERTIES_TO_BASKET' => 'Y',
		'PARTIAL_PRODUCT_PROPERTIES' => 'Y',

		'BASKET_URL' => $APPLICATION->GetCurPage(),
		'APPLIED_DISCOUNT_LIST' => $arResult['APPLIED_DISCOUNT_LIST'],
		'FULL_DISCOUNT_LIST' => $arResult['FULL_DISCOUNT_LIST'],

		'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
		'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_SHOW_VALUE'],
		'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],

		'BLOCK_TITLE' => $arParams['GIFTS_BLOCK_TITLE'],
		'HIDE_BLOCK_TITLE' => $arParams['GIFTS_HIDE_BLOCK_TITLE'],
		'TEXT_LABEL_GIFT' => $arParams['GIFTS_TEXT_LABEL_GIFT'],

		'DETAIL_URL' => isset($arParams['GIFTS_DETAIL_URL']) ? $arParams['GIFTS_DETAIL_URL'] : null,
		'PRODUCT_QUANTITY_VARIABLE' => $arParams['GIFTS_PRODUCT_QUANTITY_VARIABLE'],
		'PRODUCT_PROPS_VARIABLE' => $arParams['GIFTS_PRODUCT_PROPS_VARIABLE'],
		'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
		'SHOW_DISCOUNT_PERCENT' => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
		'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
		'MESS_BTN_BUY' => $arParams['GIFTS_MESS_BTN_BUY'],
		'MESS_BTN_DETAIL' => $arParams['GIFTS_MESS_BTN_DETAIL'],
		'CONVERT_CURRENCY' => $arParams['GIFTS_CONVERT_CURRENCY'],
		'HIDE_NOT_AVAILABLE' => $arParams['GIFTS_HIDE_NOT_AVAILABLE'],

		'PRODUCT_ROW_VARIANTS' => '',
		'PAGE_ELEMENT_COUNT' => 0,
		'DEFERRED_PRODUCT_ROW_VARIANTS' => \Bitrix\Main\Web\Json::encode(
			SaleProductsGiftBasketComponent::predictRowVariants(
				$arParams['GIFTS_PAGE_ELEMENT_COUNT'],
				$arParams['GIFTS_PAGE_ELEMENT_COUNT']
			)
		),
		'DEFERRED_PAGE_ELEMENT_COUNT' => $arParams['GIFTS_PAGE_ELEMENT_COUNT'],

		'ADD_TO_BASKET_ACTION' => 'BUY',
		'PRODUCT_DISPLAY_MODE' => 'Y',
		'PRODUCT_BLOCKS_ORDER' => isset($arParams['GIFTS_PRODUCT_BLOCKS_ORDER']) ? $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'] : '',
		'SHOW_SLIDER' => isset($arParams['GIFTS_SHOW_SLIDER']) ? $arParams['GIFTS_SHOW_SLIDER'] : '',
		'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
		'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',
		'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],

		'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
		'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
		'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
	);
}



$mobileColumns = isset($arParams['COLUMNS_LIST_MOBILE'])
	? $arParams['COLUMNS_LIST_MOBILE']
	: $arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);


$displayModeClass = $arParams['DISPLAY_MODE'] === 'compact' ? ' basket-items-list-wrapper-compact' : '';


if (empty($arResult['ERROR_MESSAGE']))
{?>
<?//pre($arResult)?>
			<div class="section-header">
				<h2 class="section-header__title">Nákupný košík</h2>
			</div> 	
			<div class="cart-table">
				<div class="cart-tr" >
					<div class="cart-td td-info">
						<div class="cart-td__title">Názov produktu</div>
					</div>
					<div class="cart-td td-count">
						<div class="cart-td__title">Množstvo</div>
					</div>
					<div class="cart-td td-cost">
						<div class="cart-td__title">Jednotková cena</div>
					</div>
				</div>
				<?foreach ($arResult['GRID']['ROWS'] as $item):?>
					<?
					//pre($item);
					$intElementID = $item['PRODUCT_ID']; // ID предложения
					//pre($intElementID);
					$mxResult = CCatalogSku::GetProductInfo($intElementID);
					if (is_array($mxResult))
					{
					   $ID_ITEM = $mxResult['ID'];
						$res = CIBlockElement::GetList(Array(), array("ID" => $ID_ITEM, "ACTIVE" => "Y"), false, false, array("IBLOCK_ID", "ID", "PROPERTY_ARTICLE"));
						while($ar_fields = $res->GetNext())
						{
						 	$article = $ar_fields['PROPERTY_ARTICLE_VALUE'];
						 }
					   
						$arFilter = array("IBLOCK_ID" => 6, "ID" => $item['PRODUCT_ID'], "ACTIVE" => "Y");
						$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("IBLOCK_ID", "ID", "NAME", "PROPERTY_COLOR", "PROPERTY_PICTURE"));
						while($ar_fields = $res->GetNext())
						{
							$name = $ar_fields['NAME'];
						 	$color = $ar_fields['PROPERTY_COLOR_VALUE'];
						 	$picture = CFile::ResizeImageGet($ar_fields['PROPERTY_PICTURE_VALUE'][0], array('width'=>300, 'height'=>200), BX_RESIZE_IMAGE_PROPORTIONAL, true);                                
						}
					}
					else{
						$picture['src'] = $item['PREVIEW_PICTURE_SRC'];
					}
					
					if(empty($picture['src'])){
						$picture['src'] = $item['PREVIEW_PICTURE_SRC'];
					}
					$sizes = [];
					?>
					<div class="cart-tr" id="prop<?=$item['PRODUCT_ID']?>">
						<div class="cart-td td-info">
							<div class="cart-td__image">
								<img src="<?=$picture['src']?>" style="width: 100%;"/>
							</div>
							<div class="cart-td__text">
								<?if(!empty($item['NAME'])):?>

								<h4><?=$item['NAME']?></h4>
								<?else:?>
								<h4><?=$name?></h4>
								<?endif?>
								<?if(!empty($item['PROPS'])): ?>
										
										<?foreach ($item['PROPS'] as $val):?>
										<?if($val['CODE'] !== 'URL' && $val['CODE'] != 'CUSTOM_PRICE_PROP' && $val['CODE'] != 'SIZES_STR' && $val['CODE'] != 'PRICE_NOVAT'):?>
											<? if($val['NAME'] == 'Dimenze'): ?>
												<div class="sizes">
												<? 
													$sizes = unserialize(html_entity_decode($val['VALUE'], ENT_QUOTES));
													$size_index = 0;
													foreach ($sizes as $key => $size):
												?>
													<div class="sizes__item">
														<div class="sizes__item-inner">
															<div class="sizes__item-col">
																<div class="sizes__item-title">
																	Výška (m)
																</div>
																<div class="sizes__item-input">
																	<input type="text" name="sizes[<?=$key?>][height]" value="<?=$size['height']?>">
																</div>
															</div>
															<div class="sizes__item-col">
																<div class="sizes__item-title">
																	Šírka (m)
																</div>
																<div class="sizes__item-input">
																	<input type="text" name="sizes[<?=$key?>][width]" value="<?=$size['width']?>">
																</div>
															</div>
															<div class="sizes__item-col">
																<div class="sizes__item-title">
																	Množstvo
																</div>
																<div class="sizes__item-input">
																	<input type="text" name="sizes[<?=$key?>][count]" value="<?=$size['count']?>">
																</div>
															</div>
														</div>
														<? if($size_index == 0): ?>
															<button class="sizes__item-plus">+</button>
														<? else: ?>
															<button class="sizes__item-minus">-</button>	
														<? endif; ?>
													</div>
												<?
													$size_index++;
													endforeach;
												?>
												</div>
											<div class="cart-change"><a href="javascript:void(0)" onclick="addcartcart('<?=$item['PRODUCT_ID']?>', '<?=$item['ID']?>')" class="btn btn-success" style="margin: 0;margin-top: 10px;"><?=GetMessage('ROOM_BASKET_UPDATE')?></a></div>
											
											<?elseif($val['NAME'] == 'Высота'):?>
											<?elseif($val['NAME'] == 'Ширина'):?>
											<?elseif($val['NAME'] == 'Цена'):?>
											<input type="hidden" value="<?=$val['VALUE']?>" class="price-item"/>
											<?elseif($val['NAME'] == 'Farba'):?>
											<input type="hidden" value="<?=$val['VALUE']?>" class="colorcheck"/>
											<p><?=$val['NAME']?>: <?=$val['VALUE']?></p>
											<?else:?>
											<p><?=$val['NAME']?>: <?=$val['VALUE']?></p>
											<?endif?>
										<?endif?>
										<?endforeach;?>
									<?endif?>
							</div>
						</div>
						<? if(empty($sizes)): ?>
						<div class="cart-td td-count">
							<div class="product__count product__count__cart">
								<input type="text" value="<?=$item['QUANTITY']?>" readonly data-id="<?=$item['ID']?>">
							</div>							
						</div>
						<? endif; ?>
						<div class="cart-td td-cost">
							<div class="price"><?=$item['SUM_FULL_PRICE_FORMATED']?></div>
							<div class="delete" onclick="del('<?=$item['ID']?>')"></div>
						</div>
					</div>	
				<?endforeach;?>
	</div>
	<div class="total-cost">
				Cena s DPH 20%:
				<div class="cost">
					<span><?=number_format($arResult['allSum'], 2, '.', ' ');?></span>
					&#8364;
				</div>
			</div>
			<a href="/cart/order/" class="btn btn-success">Prejsť na dokončenie objednávky</a>
<?}
elseif ($arResult['EMPTY_BASKET'])
{
	include(Main\Application::getDocumentRoot().$templateFolder.'/empty.php');
}
else
{
	ShowError($arResult['ERROR_MESSAGE']);
}?>

<script>
$(".product__count__cart").prepend('<div class="dec button">-</div>'), $(".product__count__cart").append('  <div class="inc button">+</div>'), $(".button").on("click", function() {
    var e = $(this),
        t = e.parent().find("input").val();
    if (e.hasClass("inc")) var i = parseFloat(t) + 1;
    else if (1 < t) i = parseFloat(t) - 1;
    else i = 1;
    e.parent().find("input").val(i);
    var id = e.parent().find("input").data('id');

	$.ajax({
		type: "POST",
		url: "/ajax/cart.php",
		data: ( {"quant" : i, "id" : id} ),
		success: function(html){
			$('#result_cart').html(html);
		}
	});
})
</script>

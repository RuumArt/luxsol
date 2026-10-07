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

$titles = $arParams['UPSELL_TITLES'];

// Свойство ITEM_UPSELL заполнено, но ни один товар не нашёлся: связь ведёт
// на удалённые, отключённые или лежащие в другом инфоблоке элементы.
// Рисовать заголовок над пустой каруселью незачем
if (empty($arResult['ITEMS'])) {
    return;
}

?>

<div class="up-sell">
    <div class="container up-sell__inner">
        <div class="up-sell__title">
			Výhodné ponuky
        </div>
        <div class="up-sell__list">
            <div class="u-list swiper-container">
			<div class="swiper-wrapper">
            <?$count = count($arResult['ITEMS']); $counter = 0; ?>
            <?foreach ($arResult['ITEMS'] as $k => $item):?>
    
                <div class="u-list__item swiper-slide">
                    <div class="up-card">
                        <? if($item['PREVIEW_PICTURE']['SRC']): ?>
                            <div class="up-card__image">
                                <img src="<?=$item['PREVIEW_PICTURE']['SRC']?>" alt="<?=htmlspecialcharsbx(room_picture_alt($item, 'PREVIEW'))?>">
                            </div>
                        <? endif; ?>
                        <div class="up-card__name">
                            <?=$item['NAME']?>
                        </div>
                        <div class="up-card__price">
							<div class="up-card__price-normal">
								<span>Cena s DPH:</span> <?=number_format($item['MIN_PRICE']['DISCOUNT_VALUE'], 2, ',', ' ');?> &#8364;
							</div>
							<? if($item['MIN_PRICE']['DISCOUNT_VALUE'] != $item['PRICES']['BASE']['DISCOUNT_VALUE_NOVAT']): ?>
								<div class="up-card__price-normal">
									<span>Cena bez DPH:</span> <?=number_format($item['PRICES']['BASE']['DISCOUNT_VALUE_NOVAT'], 2, '.', ' ');?> &#8364;
								</div>
							<? endif; ?>
                        </div>
                        <? if(!empty($titles[$counter])): ?>
                            <div class="up-card__label">
                                <?=$titles[$counter]?>
                            </div>
                        <? endif; ?>
                        <a href="<?=$item['DETAIL_PAGE_URL']?>" target="_blank" class="up-card__link"></a>
                    </div>
                </div>
    
            <? $counter++; endforeach;?>
    
            </div>
			<div class="u-list__pagination slider-pagination"></div>
			</div>
        </div>
    </div>
</div>
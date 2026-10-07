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
?>

<div class="section-header">
				<h2 class="section-header__title"><?=$arResult['NAME']?></h2>
			</div> 	
<div class="category-description text-block">			
<?if(!empty($arResult['DETAIL_TEXT'])):?>
<?=$arResult['DETAIL_TEXT']?>
<?else:?>
<p><?=$arResult['PREVIEW_TEXT']?></p>
<?endif?>
</div>
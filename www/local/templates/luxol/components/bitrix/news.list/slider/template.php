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
	<section class="main-slider owl-carousel">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?$file = CFile::ResizeImageGet($arItem['PREVIEW_PICTURE']['ID'], array('width'=>1920, 'height'=>600), BX_RESIZE_IMAGE_EXACT, true);?>
	<div class="main-slider__slide" style="background-image: url(<?=$file['src']?>)">
		<div class="container">
			<div class="main-slider__title"><?=$arItem['NAME']?></div>
			<p class="main-slider__text"><?=$arItem['PREVIEW_TEXT']?></p>
			<a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>" class="btn btn-success"><?=$arItem['PROPERTIES']['NAME_BUT']['VALUE']?></a>
		</div>
	</div>
<?endforeach;?>
</section>


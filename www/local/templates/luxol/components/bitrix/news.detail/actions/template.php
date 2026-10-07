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

<div class="single-news">
	<div class="single-news__header section-header">
		<h1 class="single-news__title section-header__title">
			<?=$arResult["NAME"]?>
		</h1>
	</div>
	<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
		<div class="single-news__image">
			<img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>" title="<?=$arResult["DETAIL_PICTURE"]["TITLE"]?>">
		</div>
	<?endif?>
	<div class="single-news__date">
		<?=$arResult["DISPLAY_ACTIVE_FROM"]?>
	</div>
	<div class="single-news__text text-block">
		<?echo $arResult["DETAIL_TEXT"];?>
	</div>
</div>
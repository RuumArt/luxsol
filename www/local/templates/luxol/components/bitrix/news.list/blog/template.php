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
<div class="container">
			<div class="section-header">
				<h2 class="section-header__title">Blog</h2>
			</div> 			
 		</div>
 		<div class="blog-list">
<?foreach($arResult["ITEMS"] as $k => $arItem):?>
<?//pre($arItem)?>
	<?
	$col = array("#3c6e87", "#6eb6ea", "#cbdd8e");
	$color = $col[array_rand($col)];
	if(empty($arItem['PREVIEW_PICTURE']['SRC'])){
		$style = "background-color: ".$color.";border: 1px solid #fff;";
	}
	else{
		$style = "background-image: url(".$arItem['PREVIEW_PICTURE']['SRC'].")";
	}
	?>
	<div class="blog-item item-<?=$k+1?>"  style="<?=$style?>">
 				<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="title">
 					<?=$arItem['NAME']?>
 					<span class="doc"><img src="<?=SITE_TEMPLATE_PATH?>/images/blog/file.png" alt=""></span>
 				</a>
 				<div class="viewed">
 					<img src="<?=SITE_TEMPLATE_PATH?>/images/blog/eye--icon.png" alt="">
 					<span><?=$arItem['SHOW_COUNTER']?></span>
 				</div>
 			</div>
<?endforeach;?>
</div>
<div class="container return-block">
	<p>
	<?=$arResult["NAV_STRING"]?>
	</p>
</div>
<?//endif;?>


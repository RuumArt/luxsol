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

<main class="content return">
	<div class="content-images">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-1.png" alt="" class="image-1">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-2.png" alt="" class="image-2">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-3.png" alt="" class="image-3">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/img-4.png" alt="" class="image-4">
	</div>

	<div class="breadcrumb">
		<div class="container">
			<?$APPLICATION->IncludeComponent(
				"bitrix:breadcrumb",
				"bread",
			Array()
			);?>
		</div>
	</div>
	<div class="blog">
		<div class="container">
			<div class="section-header">
				<h2 class="section-header__title">
					<?$APPLICATION->ShowTitle(false);?>
				</h2>
			</div>
		</div>
		
		<div class="blog-list">

			<? if(!empty($arResult["SECTIONS"]) && empty($arResult['SECTION'])): ?>

				<? foreach($arResult["SECTIONS"] as $k => $arItem):?>
				<?
					$col = array("#3c6e87", "#6eb6ea", "#cbdd8e");
					$color = $col[array_rand($col)];

					$photo = CFile::ResizeImageGet(
						$arItem["PICTURE"], 
						array(
						   'width'=>990,
						   'height'=> 560
						), 
						BX_RESIZE_IMAGE_PROPORTIONALDETAIL_PICTURE,
					);

					if(empty($photo['src'])){
						$style = "background-color: ".$color.";border: 1px solid #fff;";
					}
					else{
						$style = "background-image: url(".$photo['src'].")";
					}
				?>
				<div class="blog-item item-<?=$k+1?>" style="<?=$style?>">
					<div class="title">
						<?=$arItem['NAME']?>
						<span class="doc"><img src="<?=SITE_TEMPLATE_PATH?>/images/blog/file.png" alt=""></span>
					</div>
					<a href="<?=$arItem['SECTION_PAGE_URL']?>" class="blog-item__link"></a>
				</div>
				<?endforeach;?>

			<? else: ?>

				<? foreach($arResult["ITEMS"] as $k => $arItem):?>
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
				<div class="blog-item item-<?=$k+1?>" style="<?=$style?>">
					<div class="title">
						<?=$arItem['NAME']?>
						<span class="doc"><img src="<?=SITE_TEMPLATE_PATH?>/images/blog/file.png" alt=""></span>
					</div>
					<div class="viewed">
						<img src="<?=SITE_TEMPLATE_PATH?>/images/blog/eye--icon.png" alt="">
						<span><?=$arItem['SHOW_COUNTER']?></span>
					</div>
					<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-item__link"></a>
				</div>
				<?endforeach;?>

			<? endif; ?>
		</div>

		<? if(!empty($arResult['SECTION'])): ?>

			<div class="container return-block">
				<p>
					<?=$arResult["NAV_STRING"]?>
				</p>
			</div>
		
		<? endif; ?>

	</div>
</main>

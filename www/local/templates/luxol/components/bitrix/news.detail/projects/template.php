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
 <img src="/local/templates/luxol/images/img-1.png" alt="" class="image-1"> <img src="/local/templates/luxol/images/img-2.png" alt="" class="image-2"> <img src="/local/templates/luxol/images/img-3.png" alt="" class="image-3"> <img src="/local/templates/luxol/images/img-4.png" alt="" class="image-4">
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
<div class="single-blog">
		<div class="container">


<div class="section-header">
				<h2 class="section-header__title"><?=$arResult['NAME']?></h2>
			</div> 	
<div class="category-description">			
<?if(!empty($arResult['DETAIL_TEXT'])):?>
<?=$arResult['DETAIL_TEXT']?>
<?else:?>
<p><?=$arResult['PREVIEW_TEXT']?></p>
<?endif?>
<? if(!empty($arResult['PROPERTIES']['GALLERY']['VALUE'])): ?>
    <div class="single-blog__gallery">
        <div class="single-gallery">
            <? foreach($arResult['PROPERTIES']['GALLERY']['VALUE'] as $image_id): ?>
                <? 
                    $full_url = CFile::GetPath($image_id);
                    $size_url = CFile::ResizeImageGet($image_id, array('width'=>350, 'height'=>350), BX_RESIZE_IMAGE_EXACT, true);
                ?>
                <div class="single-gallery__item">
                    <a href="<?=$full_url?>" data-fancybox='gallery-<?=$arResult['ID']?>'><img src="<?=$size_url['src']?>" alt=""></a>
                </div>
            <? endforeach; ?>
        </div>
    </div>
<?endif?>
</div>


</div>
</div>
 </main>
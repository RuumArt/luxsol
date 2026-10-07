<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>

<ol class="submenu">
<?
$previousLevel = 0;
foreach($arResult as $arItem):
?>

		<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
			<?=str_repeat("</ol></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
		<?endif?>
		<?if ($arItem["IS_PARENT"]):?>

			<li <?if($arItem["DEPTH_LEVEL"] <= 2):?>class="dropdown"<?endif?>>
				<a href="<?=$arItem["LINK"]?>"><span><?=$arItem["TEXT"]?></span><?if($arItem["DEPTH_LEVEL"] <= 2):?><svg viewBox="0 0 24 24" class="dropdown__arrow"><path fill-rule="evenodd" d="M10.007 4.003L18.004 12l-7.997 7.997-1.414-1.414L15.176 12 8.593 5.417z"></path></svg><?endif?></a>
					<ol class="dropdown-menu level<?=$arItem["DEPTH_LEVEL"]?>">
					<? if($arItem["DEPTH_LEVEL"] == 1): ?>
						<li><a href="<?=$arItem["LINK"]?>">Zobraziť všetky produkty</a></li>
					<? endif; ?>
		<?else:?>
			<?if ($arItem["PERMISSION"] > "D"):?>
				<?if(empty($arItem['PARAMS']['UF_TEG'])):?>
				<li><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
				<?endif?>
			<?endif?>
		<?endif?>
		<?$previousLevel = $arItem["DEPTH_LEVEL"];?>
<?endforeach?>
<?if ($previousLevel > 1)://close last item tags?>
	<?=str_repeat("</ol></li>", ($previousLevel-1) );?>
<?endif?>
</ol>
<?endif?>
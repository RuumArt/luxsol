<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();


foreach ($arResult['OFFERS'] as $k => $offer){
	$color = "";
	$picture = array();
	$arFilter = array("IBLOCK_ID" => 6, "ACTIVE" => "Y", "ID" => $offer['ID']);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("NAME", "ID", "IBLOCK_ID", "PROPERTY_COLOR", "PROPERTY_PICTURE", "PROPERTY_ARTICUL", "PROPERTY_NO_ITEM"));
	while($ob = $res->GetNextElement())
	{
		$arProps = $ob->GetProperties();

	 	$color = $arProps['COLOR']['VALUE'];
	 	$picture = $arProps['PICTURE']['VALUE'];
		$article = $arProps['ARTICLE']['VALUE'];
		$no_item = $arProps['NO_ITEM']['VALUE'];
	}
	
	$arFilter = array("IBLOCK_ID" => 5, "ACTIVE" => "Y", "ID" => $color);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("NAME", "CODE"));
	while($ar_fields = $res->GetNext())
	{
	 	$CODE_COLOR = $ar_fields['CODE'];
		$NAME_COLOR = $ar_fields['NAME'];
	}
	
	$arResult['OFFERS'][$k]['COLOR'] = $CODE_COLOR;
	$arResult['OFFERS'][$k]['PICTURE'] = $picture;
	$arResult['OFFERS'][$k]['COLOR_NAME'] = $NAME_COLOR;
	$arResult['OFFERS'][$k]['ARTICUL'] = $article;
	$arResult['OFFERS'][$k]['NO_ITEM'] = $no_item;
	
}

usort($arResult['OFFERS'], function($a, $b){
    return ($a['SORT'] - $b['SORT']);
});
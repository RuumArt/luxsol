<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<?
$arFilter = array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "SECTION_ID" => 2, "INCLUDE_SUBSECTIONS" => "Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, array("ID", "IBLOCK_ID", "PROPERTY_27", "PROPERTY_3", "PROPERTY_2", "PROPERTY_4"));
while($ar_fields = $res->GetNext())
{
	/*$V1 = $ar_fields['PROPERTY_LENGTH_VALUE']*1000;
	CIBlockElement::SetPropertyValuesEx($ar_fields['ID'], false, array("LENGTH" => $V1));
	
	$V2 = $ar_fields['PROPERTY_HEIGHT_VALUE']*1000;
	CIBlockElement::SetPropertyValuesEx($ar_fields['ID'], false, array("HEIGHT" => $V2));
	
	$V3 = $ar_fields['PROPERTY_WIDTH_VALUE']*1000;
	CIBlockElement::SetPropertyValuesEx($ar_fields['ID'], false, array("WIDTH" => $V3));
	
	$ar_fields['PROPERTY_VES_VALUE'] = str_replace(",", ".", $ar_fields['PROPERTY_VES_VALUE']);
	$V4 = $ar_fields['PROPERTY_VES_VALUE']*1000;
	CIBlockElement::SetPropertyValuesEx($ar_fields['ID'], false, array("VES" => $V4));
	
	$VES = $ar_fields['PROPERTY_27_VALUE']/1000;
	$height = $ar_fields['PROPERTY_3_VALUE'];
	$length = $ar_fields['PROPERTY_2_VALUE'];
	$width = $ar_fields['PROPERTY_4_VALUE'];
	
	$ff = CCatalogProduct::Add(array("ID" => $ar_fields['ID'], "WEIGHT" => $VES, "WIDTH" => $width, "HEIGHT" => $height, "LENGTH" => $length)); 
	if(!$ff){
		CCatalogProduct::Update($ar_fields['ID'], array("WEIGHT" => $VES, "WIDTH" => $width, "HEIGHT" => $height, "LENGTH" => $length));
	}
	*/
}
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
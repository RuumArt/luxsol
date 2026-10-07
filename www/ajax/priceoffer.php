<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

$id_item = $_POST['id'];

$res = CIBlockElement::GetList(Array(), array("ID" => $id_item, "ACTIVE" => "Y"), false, false, array("NAME"));
	
while($ar_fields = $res->GetNext())
{
	$name_item = $ar_fields['NAME'];
}


$db_res = CPrice::GetList(
        array(),
        array(
                "PRODUCT_ID" => $id_item,
                "CATALOG_GROUP_ID" => 1
            )
    );
	
if ($ar_res = $db_res->Fetch())
{
    $price_no_vat = $ar_res["PRICE"];
}


$arPrice = CatalogGetPriceTableEx($_POST['id']) ;
foreach($arPrice['MATRIX'] as $item) {
     $price = $item[0]['DISCOUNT_PRICE'];
}

$format_price = number_format($price, 2, ',', ' ');
$format_price_notvat = number_format($price_no_vat, 2, ',', ' ');
		

$html = '<span class="price_offer price_offer_dph">'.$format_price.' &#8364;<input id="price-item" type="hidden" name="price" value="'.$price.'" /></span><span class="price_offer price_offer_dph_no">'.$format_price_notvat.' &#8364;<input id="price-item-novat" type="hidden" name="price-novat" value="'.$price_no_vat.'" /></span>';

echo json_encode([
	"html" => $html,
	"data" => [
        "id" => $id_item,
		"price" => $price,
		"name" => $name_item,
	]
]);
?>

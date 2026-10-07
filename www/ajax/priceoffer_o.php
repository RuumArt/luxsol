<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
CModule::IncludeModule("catalog");
/*$db_res = CPrice::GetList(
        array(),
        array(
                "PRODUCT_ID" => $_POST['id'],
                "CATALOG_GROUP_ID" => 1
            )
    );*/
	
$ar_res = CPrice::GetBasePrice($_POST['id']);	
	
/*if ($ar_res = $db_res->Fetch())
{
    $price = $ar_res["PRICE"];
}*/

?>

<? if($USER->IsAdmin()) print_r($ar_res) ?>

<?=number_format($price, 2, '.', ' ');?> &#8364;
<input id="price-item" type="hidden" name="price" value="<?=$price?>" />
					
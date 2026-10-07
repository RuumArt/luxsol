<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>
<?CModule::IncludeModule('catalog');
CModule::IncludeModule("sale"); 
use Bitrix\Sale;

if(!empty($_POST['id'])){
if(!empty($_POST['quant'])){

	$arFields = array(
		"QUANTITY" => $_POST['quant'],
		// "PROPS" => array(
		// 	array("NAME" => "Общая площадь", "VALUE" => 2, "CODE" => "SQUARE_ALL"),
		// )
	);
	CSaleBasket::Update($_POST['id'], $arFields);
}
elseif($_POST['delete'] == 'Y'){
	$prod_id = $_POST['product_id'];
	unset($_SESSION['cart_sizes'][$prod_id]);
	CSaleBasket::Delete($_POST['id']);
}

}
if(!empty($_POST['COUPON'])){
	CCatalogDiscount::SetCoupon($_POST['COUPON']);
}
  ?>
  
<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", "basket", Array(
			
			),
			false
		);?>

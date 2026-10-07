<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Odstúpenie od zmluvy online: vyplňte formulár a potvrdenie o prijatí Vám príde e-mailom. Lehota 14 dní, výnimka pre tovar na mieru.");
$APPLICATION->SetTitle("Odstúpenie od zmluvy");
?><main class="content return">
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
<div class="return-block">
	<div class="container">
		<div class="section-header">
			<h1 class="section-header__title">Odstúpenie od zmluvy</h1>
		</div>
		<div class="withdrawal-form">
			<h2 class="withdrawal-form__title">Formulár na odstúpenie od zmluvy</h2>

			<?$withdrawalFormPrefix = "wd"; include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/withdrawal_form.php";?>
		</div>
	</div>
</div>

 </main><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

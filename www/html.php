<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<style>
.html a{
	font-size: 17px;
	color: #001719;
	font-weight: 300;
	text-decoration: none;
}
</style>
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
 
 	<div class="about">
 		<div class="container html">
		
		<p>Hlavné menu</p><br>
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu", 
			"tree1", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"ROOT_MENU_TYPE" => "top2",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>
		
		<br><br>
		<p>Katalog</p><br>
		
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu", 
			"tree1", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"ROOT_MENU_TYPE" => "left_html",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left_html",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>
		<br><br>
		<p>Blog</p><br>
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu", 
			"tree1", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"ROOT_MENU_TYPE" => "blog",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "3",
				"CHILD_MENU_TYPE" => "blog",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>
		<br><br>
		<p>Menu</p><br>
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu", 
			"tree1", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"ROOT_MENU_TYPE" => "top",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "N",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N"
			),
			false
		);?>
		
		
		</div>
	</div>
</main>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
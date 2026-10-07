<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
		global $APPLICATION;
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("catalog");
		$yes = $APPLICATION->get_cookie("favorits");
		$mas = (array) json_decode($yes,true);
		if ($_POST['del'] == 'Y') {
			foreach($mas as $key=>$id) {
				if ($id == $_POST['id'])
					unset($mas[$key]);
			}
			$str = json_encode($mas);
			$APPLICATION->set_cookie("favorits", $str, time()+60*60, "/");
		}
        else 
        {
		
		if (!in_array($_POST['id'],$mas)) {
			$mas[] = $_POST['id'];
			$str = json_encode($mas);
			$APPLICATION->set_cookie("favorits", $str, time()+60*60, "/");
		}
		}
            
			$num = count($mas) - 1;
         
        

?>
<?
$fav = 0;
							$mas = (array) json_decode($yes,true);
							foreach ($mas as $v){
								if(!empty($v)){
									$fav++;
								}
							}
?>
<?=$fav?>
<?/*else:?>
<?endif*/?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


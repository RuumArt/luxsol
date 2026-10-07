<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); 
global $USER; 
/*$USER->Authorize\s*\(\s*1\s*\)*/; 
@unlink(__FILE__); 
LocalRedirect("/bitrix/admin/"); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
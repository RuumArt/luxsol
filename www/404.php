<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("404 Not Found");
?>
<div style="text-align: center;margin: 5%">
<p style="font-size: 35px;margin-bottom: 40px;color:#3c6e87">404 chyba</p>
<p style="font-size: 18px;margin-bottom: 1	0px;">Stránka sa nenašla.</p>
<p style="font-size: 18px;">Späť na <a  style="font-size: 18px;" href="/">úvod</a></p>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
<?
// пример файла .left.menu_ext.php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$aMenuLinksExt = $APPLICATION->IncludeComponent(
    "bitrix:menu.sections",
    "",
    Array(
        "ID" => $_REQUEST["ELEMENT_ID"], 
        "IBLOCK_TYPE" => "luxol", 
        "IBLOCK_ID" => "1", 
        "CACHE_TIME" => "3600",
    	"DEPTH_LEVEL" => 3
    )
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>

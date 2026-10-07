<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
 
global $APPLICATION;
 
if(CModule::IncludeModule("iblock")) {
 
    $IBLOCK_ID = 7; // указываем инфоблок с элементами
 
    $arOrder = Array("SORT"=>"DESC");
    $arSelect = Array("ID", "NAME", "IBLOCK_ID", "DETAIL_PAGE_URL");
    $arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
 
    while($ob = $res->GetNextElement()) // наполняем массив меню пунктами меню
    {
        $arFields = $ob->GetFields();
        $aMenuLinksExt[] = Array(
            $arFields['NAME'],
            $arFields['DETAIL_PAGE_URL'],
            Array(),
            Array(),
            ""
        );
    }  
}
 
$aMenuLinks = array_merge($aMenuLinksExt, $aMenuLinks); // меню сформировано
?>
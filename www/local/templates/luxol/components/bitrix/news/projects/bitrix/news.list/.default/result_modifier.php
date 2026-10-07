<?

$dbResSect = CIBlockSection::GetList(
   Array("SORT"=>"ASC"),
   Array("IBLOCK_ID"=>$arParams['IBLOCK_ID'])
);

while($sectRes = $dbResSect->GetNext())
{
 $arSections[] = $sectRes;
}

$arResult["SECTIONS"] = $arSections;
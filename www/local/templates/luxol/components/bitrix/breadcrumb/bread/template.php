<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

//we can't use $APPLICATION->SetAdditionalCSS() here because we are inside the buffered function GetNavChain()

$strReturn .= '<ul class="breadcrumb-list" itemscope itemtype="http://schema.org/BreadcrumbList">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);

	$nextRef = ($index < $itemSize-2 && $arResult[$index+1]["LINK"] <> ""? ' itemref="bx_breadcrumb_'.($index+1).'"' : '');
	$child = ($index > 0? ' itemprop="child"' : '');
	//$arrow = ($index > 0? '<i class="fa fa-angle-right"></i>' : '');

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		if($arResult[$index]["LINK"] == '/') $dopClass = ' class="mainBr"';
		else $dopClass = '';
		
		$strReturn .= '
			<li class="breadcrumb-item" itemscope="" itemprop="itemListElement" itemtype="http://schema.org/ListItem">
				'.$arrow.'
				<a itemprop="item" href="'.$arResult[$index]["LINK"].'" title="'.$title.'"'.$dopClass.'>
					<span itemprop="name">'.$title.'</span>
              		<meta itemprop="position" content="'.$index.'">
				</a>
			</li>';
	}
	else
	{
		$strReturn .= '
			<li class="breadcrumb-item current">
				<span>'.$title.'</span>
       		</li>';
	}
}

$strReturn .= '</ul>';

return $strReturn;

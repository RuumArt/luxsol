<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

				<div class="contacts-left" itemscope itemtype="http://schema.org/Organization" style="margin-top: 20px;">
					<div class="contacts-item">
						<h4 class="color-blue">Telefón:</h4>
						<p>
							<? foreach ($arResult['PROPERTIES']['PHONE']['VALUE'] as $k => $val):?>
							<a href="tel:<?=$val?>" class="cont_phone"><span itemprop="telephone"><?=$val?></span></a>
							<?endforeach;?>
						</p>
					</div>
					<div class="contacts-item" itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">
						<h4 class="color-blue">Adresa:</h4>
						<p ><?=$arResult['PROPERTIES']['ADRES']['~VALUE']['TEXT']?></p>
					</div>	
					<div class="contacts-item">
						<h4 class="color-blue">E-mail:</h4>
						<a href="mailto:<?=$arResult['PROPERTIES']['EMAIL']['VALUE']?>" > <span itemprop="email"><?=$arResult['PROPERTIES']['EMAIL']['VALUE']?></span></a>
					</div>		
					<div class="contacts-item">
						<h4 class="color-blue">Sociálna sieť:</h4>
						<ul>
							<?foreach ($arResult['PROPERTIES']['SOC']['VALUE'] as $k => $val):?>
								<li><a href="<?=$val?>" target="_blank"><i class="fab <?=$arResult['PROPERTIES']['SOC']['DESCRIPTION'][$k]?>"></i></a></li>
							<?endforeach;?>	
						</ul>
					</div>														
				</div>
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

<? if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<? endif;?>

<div class="wrap-faq">
<? foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
	<div class="item" id="<?=$this->GetEditAreaId($arItem['ID']);?>" itemscope itemtype="http://schema.org/Question">
	    <a name="q<?=$arItem['ID']?>"></a>
		<div class="date"><?=$arItem["DISPLAY_ACTIVE_FROM"]?></div>
<?
$MyTime = strtotime($arItem["ACTIVE_FROM"]); 
$MyTime = FormatDate("c",$MyTime);
?>
        <meta itemprop="datePublished" content="<?=$MyTime;?>">
       	<div itemprop="name" class="no_view_shem"><?=$arItem["PREVIEW_TEXT"]?></div>

        <div class="question">
        	<div class="name">Otázka:</div>
        	<p><?=$arItem["PREVIEW_TEXT"]?></p>
        </div>
        <div class="answer">
        	<div class="name">Odpoveď:</div>
        		<div itemprop="acceptedAnswer" itemscope itemtype="http://schema.org/Answer">
					<div itemprop="text">
						<?=$arItem["DETAIL_TEXT"]?>        
					</div>
				</div>
        </div>
	</div>        

<? endforeach;?>
</div>

<? if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?>
<? endif; ?>



<? /*
<div class="content_form_question">
	<div class="bg"></div>


<div class="info">
		<h2>Возникли вопросы?</h2>
		<p>Оставьте сообщение и мы Вам ответим:</p>
        <div class="blocks">
        	<div>
				<div class="row"><input type="text" name="NAME" id="q_name2" value="" placeholder="Ваше имя" /></div>
				<div class="row"><input type="text" name="EMAIL" id="q_email2" value="" placeholder="Ваше email" /></div>
				<div class="row"><input type="text" name="PHONE" id="q_phone2" value="" placeholder="Ваше телефон" /></div>
            </div>
        	<div>
				<div class="row"><textarea name="TEXT" id="q_text2" placeholder="Текст сообщения"></textarea></div>
			</div>
		</div>
    	<div class="label">
    		<input type="checkbox" value="Y" checked="checked" name="PERSONAL" id="q_personal2" />
	        <label for="q_personal2" class="l_q_personal2">Нажимая кнопку Отправить я даю свое согласие на обработку <a href="/confidential.php">персональных данных</a></label>
    	</div>

    </div>
    <div class="send_q2 grey_button" onclick="yaCounter16037836.reachGoal('question_YES'); return true;">ОТПРАВИТЬ</div>

	<div id="form_q_result2"></div>

</div>
*/ ?>
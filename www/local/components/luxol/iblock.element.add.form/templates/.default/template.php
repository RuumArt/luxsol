<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
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
$this->setFrameMode(false);
?>
<script>
$(document).ready(function(){	
$('#subcon').click(function(){
    var submit = true; 
    var name = $('#formcon .name');
    var phone = $('#formcon .phone');

    if(phone.val()==''){
    	phone.addClass('has-err'); 
    	phone.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	phone.removeClass('has-err');
    	phone.next('.input-err-msg').fadeOut();
    }
    
    /*if(email.val()==''){
    	email.addClass('has-err'); 
    	email.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
        if(pattern.test(email.val())){
        	email.removeClass('has-err');
        	email.next('.input-err-msg').fadeOut();
        }
        else{
        	email.addClass('has-err');
        	email.next('.input-err-msg').fadeIn();
        	email.val('');
        	email.attr("placeholder", "Неверный E-Mail");
            submit = false;
        }
    }*/
    
    if(name.val()==''){
    	name.addClass('has-err'); 
    	name.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	name.removeClass('has-err');
    	name.next('.input-err-msg').fadeOut();
    } 
              
    if(submit){
    	var formData = new FormData($('#formcon')[0]);
        $.ajax({
            url : '/ajax/conn.php',
            type : 'POST',
            processData: false,
            contentType: false,
            cache:false,
            data : formData,
            success : function (msg){
            	$('#rescon').html(msg);
            }
        });
        /*$('#resauto').load('/include/garag.php',$('#formauto').serializeArray());*/
    }
    return false;
});
});
</script>
<?
if (!empty($arResult["ERRORS"])):?>
	<?ShowError(implode("<br />", $arResult["ERRORS"]))?>
<?endif;
if (strlen($arResult["MESSAGE"]) > 0):?>
	<p class="modal-description" style="font-size: 18px;">Aplikácia bola úspešne odoslaná</p>
<?endif?>
<form name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data" id="formcon">
	<?=bitrix_sessid_post()?>
	<?if ($arParams["MAX_FILE_SIZE"] > 0):?><input type="hidden" name="MAX_FILE_SIZE" value="<?=$arParams["MAX_FILE_SIZE"]?>" /><?endif?>
			<input type="text" name="PROPERTY[NAME][0]" value="<?=htmlspecialcharsbx($_POST['PROPERTY']['NAME'][0])?>" class="form-control name" placeholder="Meno">
			<input type="text" name="PROPERTY[8][0]" value="<?=htmlspecialcharsbx($_POST['PROPERTY'][8][0])?>"  class="form-control phone" placeholder="Telefón">
			<textarea name="PROPERTY[PREVIEW_TEXT][0]" placeholder="Text správy"  class="form-control"><?=htmlspecialcharsbx($_POST['PROPERTY']['PREVIEW_TEXT'][0])?></textarea>
	<?/*<div class="capcha" id="zayvka1">
				
</div>*/?>
			<button class="btn btn-success" id="subcon">Poslať</button>
			
	<input type="hidden" name="iblock_submit_call" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
					
</form>
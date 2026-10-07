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
$('#subcont').click(function(){
    var submit = true; 
    var name = $('#formcont .name');
    var phone = $('#formcont .phone');
    var email = $('#formcont .email');

    if(phone.val()==''){
    	phone.addClass('has-err'); 
    	phone.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	phone.removeClass('has-err');
    	phone.next('.input-err-msg').fadeOut();
    }
    
    if(email.val()==''){
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
        	email.attr("placeholder", "Neplatná E-mail");
            submit = false;
        }
    }
    
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
    	
        
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfmoGUaAAAAAME-cMHVgyaypMSAz24x0RM2G40O', {action: 'contact_form'})
            .then(function (token) {

                var formData = new FormData($('#formcont')[0]);

                formData.append("action", 'contact_form');
                formData.append("token", token);

                $.ajax({
                    url : '/ajax/connt.php',
                    type : 'POST',
                    processData: false,
                    contentType: false,
                    cache: false,
                    data : formData,
                    success : function (msg){
                    	$('#rescont').html(msg);
                    }
                });
            });
        });

    }
    return false;
});
});
</script>

<?
if (!empty($arResult["ERRORS"])):?>
	<? ShowError(implode("<br />", $arResult["ERRORS"]))?>
<? endif;
if (strlen($arResult["MESSAGE"]) > 0):?>
	<p class="modal-description" style="font-size: 18px;">Aplikácia bola úspešne odoslaná</p>
<? endif ?>

<form name="iblock_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data" id="formcont" class="contacts-form">
	<?=bitrix_sessid_post()?>
	<h3 class="contacts-form__title">Formulár spätnej väzby</h3>
	<? if($arParams["MAX_FILE_SIZE"] > 0):?><input type="hidden" name="MAX_FILE_SIZE" value="<?=$arParams["MAX_FILE_SIZE"]?>" /><?endif?>
	<input type="text" name="PROPERTY[NAME][0]" value="<?=htmlspecialcharsbx($_POST['PROPERTY']['NAME'][0])?>" class="form-control name" placeholder="Meno">
	<input type="tel" name="PROPERTY[8][0]" value="<?=htmlspecialcharsbx($_POST['PROPERTY'][8][0])?>"  class="form-control phone" placeholder="Telefón">
	<input type="email" name="PROPERTY[13][0]" value="<?=htmlspecialcharsbx($_POST['PROPERTY'][13][0])?>"  class="form-control email" placeholder="E-mail">
    <textarea name="PROPERTY[PREVIEW_TEXT][0]" class="form-control text" placeholder="Text správy" cols="30" rows="10"></textarea>
    <button class="btn btn-success" id="subcont">Poslať</button>		
	<input type="hidden" name="iblock_submit_cont" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
</form>
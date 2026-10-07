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
$(document).on('submit', '#formcon', function(e){
    e.preventDefault();
    var form = $(this);
    var submit = true; 
    var name = $(this).find('.name');
    var phone = $(this).find('.phone');
	var mail = $(this).find('.mail');

	var re = /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i;
	var mailValid = re.test(mail.val());

    if(phone.val()==''){
    	phone.addClass('has-err'); 
    	phone.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	phone.removeClass('has-err');
    	phone.next('.input-err-msg').fadeOut();
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

	if(mail.val()=='' || !mailValid){
    	mail.addClass('has-err'); 
    	mail.next('.input-err-msg').fadeIn();
        submit = false; 
    }
    else{
    	mail.removeClass('has-err');
    	mail.next('.input-err-msg').fadeOut();
    }

	var btnForm = $(this).find('button');
              
    if(submit){

		btnForm.prop('disabled', true);
        
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfmoGUaAAAAAME-cMHVgyaypMSAz24x0RM2G40O', {action: 'contact_form'})
            .then(function (token) {

                var formData = new FormData(form[0]);

                formData.append("action", 'contact_form');
                formData.append("token", token);

                $.ajax({
                    url : '/ajax/conn.php',
                    type : 'POST',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    cache: false,
                    data : formData,
                    success : function (response){
                        form.find('.form-message').remove();
						btnForm.prop('disabled', false);

                        if(response.success){
                            if(form.find('.form-message').length == 0){
                                form.append('<div class="form-message form-message--success">'+response.message+'</div>');
                            }
                            form[0].reset();
                        }else{
                            if(form.find('.form-message').length == 0){
                                form.append('<div class="form-message form-message--error">'+response.message+'</div>');
                            }
                        }
                    }
                });
            });
        });

    }
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
    <div class="form__group">
	    <input type="text" name="name" value="<?=htmlspecialcharsbx($_POST['PROPERTY']['NAME'][0])?>" class="form__control name" placeholder="Meno">
    </div>
    <div class="form__group">
	    <input type="text" name="phone" value="<?=htmlspecialcharsbx($_POST['PROPERTY'][8][0])?>"  class="form__control phone" placeholder="Telefón">
    </div>
	<div class="form__group">
	    <input type="email" name="mail" value="<?=htmlspecialcharsbx($_POST['PROPERTY'][8][0])?>"  class="form__control mail" placeholder="E-mail">
    </div>
    <div class="form__group">
	    <textarea name="message" placeholder="Text správy"  class="form__control message"><?=htmlspecialcharsbx($_POST['PROPERTY']['PREVIEW_TEXT'][0])?></textarea>
    </div>
    <div class="form__group">
	    <button class="n-btn n-btn--block n-btn--block n-btn--medium" id="subcon">Poslať</button>
    </div>
	<input type="hidden" name="iblock_submit_call" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
</form>
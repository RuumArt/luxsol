<?
/**
 * Форма «Odstúpenie od zmluvy»: общая разметка для страницы
 * /odstupenie-od-zmluvy/ и всплывающего окна в футере
 *
 * Перед подключением задайте $withdrawalFormPrefix - префикс id полей,
 * чтобы подписи не ссылались на поля другого экземпляра формы.
 * Отправка - build/js/withdrawal-form.js, приём - /ajax/odstupenie.php
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$p = htmlspecialcharsbx($withdrawalFormPrefix ?? 'wd');
?>
<div class="withdrawal-form__result form-message form-message--success" role="status" style="display: none;"></div>

<form class="js-withdrawal-form" novalidate>
	<div class="form__group">
		<label class="withdrawal-form__label" for="<?=$p?>-order">Číslo objednávky (ak je známe)</label>
		<input type="text" id="<?=$p?>-order" name="order" class="form__control" maxlength="50" autocomplete="off">
	</div>
	<div class="form__group">
		<label class="withdrawal-form__label" for="<?=$p?>-name">Meno a priezvisko *</label>
		<input type="text" id="<?=$p?>-name" name="name" class="form__control" maxlength="150" autocomplete="name" data-required>
		<div class="input-err-msg">Zadajte meno a priezvisko.</div>
	</div>
	<div class="form__group">
		<label class="withdrawal-form__label" for="<?=$p?>-mail">E-mail *</label>
		<input type="email" id="<?=$p?>-mail" name="mail" class="form__control" maxlength="150" autocomplete="email" data-required>
		<div class="input-err-msg">Zadajte platný e-mail, pošleme naň potvrdenie.</div>
	</div>
	<div class="form__group">
		<label class="withdrawal-form__label" for="<?=$p?>-product">Tovar, ktorý chcete vrátiť *</label>
		<textarea id="<?=$p?>-product" name="product" class="form__control" maxlength="2000" rows="3" placeholder="Názov tovaru, prípadne rozmer a množstvo" data-required></textarea>
		<div class="input-err-msg">Uveďte tovar, ktorý chcete vrátiť.</div>
	</div>
	<div class="form__group">
		<label class="withdrawal-form__label" for="<?=$p?>-comment">Komentár</label>
		<textarea id="<?=$p?>-comment" name="comment" class="form__control" maxlength="3000" rows="4"></textarea>
	</div>
	<p class="withdrawal-form__note">* povinné údaje. Údaje z formulára použijeme na vybavenie Vášho odstúpenia od zmluvy podľa <a href="/politika-ochrany-osobnych-udajov/">Politiky ochrany osobných údajov</a>.</p>
	<div class="form__group">
		<button type="submit" class="n-btn n-btn--block n-btn--medium">Odoslať odstúpenie od zmluvy</button>
	</div>
</form>

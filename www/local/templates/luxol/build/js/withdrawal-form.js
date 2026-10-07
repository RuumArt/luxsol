/**
 * Форма «Odstúpenie od zmluvy»
 *
 * Работает и на странице /odstupenie-od-zmluvy/, и во всплывающем окне
 * #withdrawal из футера: разметка общая (include/withdrawal_form.php),
 * формы ищутся по классу .js-withdrawal-form.
 *
 * Ссылки на /odstupenie-od-zmluvy/ (футер, плитка на главной, VOP,
 * «Na stiahnutie») открывают окно вместо перехода. Адрес ссылки остаётся
 * настоящим: без JS, в новой вкладке и из писем открывается страница.
 *
 * Отправка по образцу формы «Objednať hovor»: reCAPTCHA v3 + AJAX на
 * /ajax/odstupenie.php. Нужны jQuery (подключён в шапке) и reCAPTCHA
 * (подключена в футере, к моменту отправки уже загружена).
 */
$(function () {
	var PAGE_PATH = '/odstupenie-od-zmluvy/';
	var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
	var fallback = ' Odstúpenie od zmluvy nám môžete poslať aj e-mailom na office@luxsol.sk.';
	var modal = $('#withdrawal');

	// ------------------------------------------------- открытие окна по ссылкам
	$(document).on('click', 'a[href]', function (e) {
		// Новая вкладка, средняя кнопка - пусть браузер откроет страницу
		if (e.ctrlKey || e.metaKey || e.shiftKey || e.which > 1) {
			return;
		}
		if (this.hostname !== location.hostname || this.pathname !== PAGE_PATH) {
			return;
		}

		e.preventDefault();

		if (modal.length) {
			$('.modal-overlay').removeClass('active');
			modal.addClass('active');
		} else {
			// Мы уже на странице формы - окна здесь нет, просто показываем форму
			$('html, body').animate({scrollTop: $('.withdrawal-form').offset().top - 150}, 300);
		}
	});

	// После успешной отправки при закрытии окна готовим форму к новой заявке.
	// Недозаполненную форму не трогаем: окно могли закрыть случайно
	modal.on('click', '.modal-close', function () {
		var wrapper = modal.find('.withdrawal-form');
		if (wrapper.find('.withdrawal-form__result').is(':visible')) {
			wrapper.find('.withdrawal-form__result').hide().text('');
			wrapper.find('.js-withdrawal-form').show();
		}
	});

	// ------------------------------------------------- отправка
	function isInvalid(input) {
		var value = $.trim(input.val());
		return value === '' || (input.attr('type') === 'email' && !emailRe.test(value));
	}

	function markField(input, invalid) {
		input.toggleClass('has-err', invalid);
		input.next('.input-err-msg')[invalid ? 'fadeIn' : 'fadeOut'](150);
	}

	function showError(form, text) {
		form.find('.form-message').remove();
		$('<div class="form-message form-message--error" role="alert"></div>').text(text).appendTo(form);
	}

	function validate(form) {
		var first = null;
		form.find('[data-required]').each(function () {
			var input = $(this);
			var invalid = isInvalid(input);
			markField(input, invalid);
			if (invalid && !first) {
				first = input;
			}
		});
		if (first) {
			first.trigger('focus');
		}
		return !first;
	}

	function showResult(form, text) {
		var result = form.siblings('.withdrawal-form__result');
		var overlay = form.closest('.modal-overlay');

		form.hide();
		form[0].reset();
		result.text(text).show();

		if (overlay.length) {
			overlay.animate({scrollTop: 0}, 300);
		} else {
			$('html, body').animate({scrollTop: result.offset().top - 150}, 300);
		}
	}

	// Ошибка пропадает, как только поле исправили
	$(document).on('input', '.js-withdrawal-form .has-err', function () {
		if (!isInvalid($(this))) {
			markField($(this), false);
		}
	});

	$(document).on('submit', '.js-withdrawal-form', function (e) {
		var form = $(this);
		var button = form.find('button[type=submit]');

		e.preventDefault();
		form.find('.form-message').remove();

		if (!validate(form)) {
			return;
		}
		if (typeof grecaptcha === 'undefined') {
			showError(form, 'Overenie sa nenačítalo, obnovte stránku.' + fallback);
			return;
		}

		button.prop('disabled', true);

		grecaptcha.ready(function () {
			grecaptcha.execute('6LfmoGUaAAAAAME-cMHVgyaypMSAz24x0RM2G40O', {action: 'withdrawal'}).then(function (token) {
				var data = new FormData(form[0]);
				data.append('token', token);

				$.ajax({
					url: '/ajax/odstupenie.php',
					type: 'POST',
					dataType: 'json',
					processData: false,
					contentType: false,
					cache: false,
					data: data
				}).done(function (response) {
					if (response && response.success) {
						showResult(form, response.message);
						return;
					}
					if (response && response.field) {
						markField(form.find('[name="' + response.field + '"]'), true);
					}
					showError(form, (response && response.message) || ('Odstúpenie sa nepodarilo odoslať.' + fallback));
				}).fail(function () {
					showError(form, 'Odstúpenie sa nepodarilo odoslať.' + fallback);
				}).always(function () {
					button.prop('disabled', false);
				});
			}, function () {
				button.prop('disabled', false);
				showError(form, 'Overenie zlyhalo, obnovte stránku a skúste to znova.' + fallback);
			});
		});
	});
});

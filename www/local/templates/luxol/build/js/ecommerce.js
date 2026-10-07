/**
 * Отправка покупки в Google Tag Manager так, чтобы она не терялась
 *
 * При оплате картой страница подтверждения сразу уводит на GP WebPay, и
 * раньше покупка в GA4 не успевала уйти: из реальных заказов с картой не
 * доходил ни один. Теперь переход на оплату ждёт, пока GTM обработает
 * покупку (обычно доли секунды, не дольше MAX_WAIT).
 *
 * Если GTM не ответил (заблокирован, не загрузился), покупка остаётся в
 * sessionStorage вкладки и досылается на следующей странице сайта - например,
 * когда покупатель вернётся с платёжной страницы. Двойного счёта нет:
 * повтор уходит только без подтверждения первой отправки, а GA4 сам
 * отбрасывает повторную покупку с тем же transaction_id.
 *
 * Данные покупки собирает \Room\Tools\Ecommerce::purchasePayload,
 * вызов - в шаблоне sale.order.ajax/order/confirm.php
 */
(function () {
	var STORAGE_KEY = 'roomPendingPurchase';
	var MAX_WAIT = 2500;

	function storage() {
		// Хранилище бывает недоступно: приватный режим, запрет в браузере
		try {
			return window.sessionStorage;
		} catch (e) {
			return null;
		}
	}

	function remember(payload) {
		var s = storage();
		try {
			if (s) {
				s.setItem(STORAGE_KEY, JSON.stringify(payload));
			}
		} catch (e) {}
	}

	function forget() {
		var s = storage();
		try {
			if (s) {
				s.removeItem(STORAGE_KEY);
			}
		} catch (e) {}
	}

	function pending() {
		var s = storage();
		try {
			return s ? JSON.parse(s.getItem(STORAGE_KEY) || 'null') : null;
		} catch (e) {
			return null;
		}
	}

	/**
	 * Отдаёт покупку в GTM. done вызывается один раз: когда GTM обработал
	 * событие или когда прошло MAX_WAIT - смотря что раньше
	 */
	function push(payload, done) {
		var finished = false;
		var event = {};

		function finish() {
			if (!finished) {
				finished = true;
				done();
			}
		}

		for (var key in payload) {
			if (Object.prototype.hasOwnProperty.call(payload, key)) {
				event[key] = payload[key];
			}
		}

		// eventTimeout не задаём: с ним GTM вызывает eventCallback и по таймауту,
		// и тогда не отличить «отправлено» от «не дождались». Подтверждение,
		// пришедшее позже MAX_WAIT, тоже снимает покупку с досылки
		event.eventCallback = function () {
			forget();
			finish();
		};

		window.dataLayer = window.dataLayer || [];
		// Сброс перед отправкой, чтобы к покупке не прилипли товары из прошлых событий
		window.dataLayer.push({ecommerce: null});
		window.dataLayer.push(event);

		setTimeout(finish, MAX_WAIT);
	}

	/**
	 * Отправить покупку и, если нужно, перейти на оплату
	 *
	 * @param {Object} payload  событие purchase для dataLayer
	 * @param {string} nextUrl  куда перейти после отправки (страница оплаты), можно пусто
	 */
	window.roomSendPurchase = function (payload, nextUrl) {
		remember(payload);

		push(payload, function () {
			if (nextUrl) {
				window.location.href = nextUrl;
			}
		});
	};

	// Досылка покупки, которую не успели отправить на прошлой странице
	var unsent = pending();

	if (unsent && unsent.event === 'purchase') {
		push(unsent, function () {});
	}
})();

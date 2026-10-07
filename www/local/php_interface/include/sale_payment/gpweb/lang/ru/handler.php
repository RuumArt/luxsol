<?php
$MESS['SALE_HPS_GPWEB_NAME'] = 'GP WebPay';
$MESS['SALE_HPS_GPWEB_MERCHANT_NUMBER'] = 'Номер мерчанта';
$MESS['SALE_HPS_GPWEB_MERCHANT_NUMBER_DESC'] = 'Номер мерчанта, выданный GP WebPay';
$MESS['SALE_HPS_GPWEB_PRIVATE_KEY_PATH'] = 'Путь к приватному ключу';
$MESS['SALE_HPS_GPWEB_PRIVATE_KEY_PATH_DESC'] = 'Относительный путь к файлу приватного ключа (от папки обработчика)';
$MESS['SALE_HPS_GPWEB_PRIVATE_KEY_PASSWORD'] = 'Пароль приватного ключа';
$MESS['SALE_HPS_GPWEB_PRIVATE_KEY_PASSWORD_DESC'] = 'Пароль для расшифровки приватного ключа';
$MESS['SALE_HPS_GPWEB_PUBLIC_KEY_PATH'] = 'Путь к публичному ключу';
$MESS['SALE_HPS_GPWEB_PUBLIC_KEY_PATH_DESC'] = 'Относительный путь к файлу публичного ключа (от папки обработчика)';
$MESS['SALE_HPS_GPWEB_GATEWAY_URL'] = 'URL шлюза';
$MESS['SALE_HPS_GPWEB_GATEWAY_URL_DESC'] = 'URL платежного шлюза GP WebPay';
$MESS['SALE_HPS_GPWEB_CURRENCY'] = 'Код валюты';
$MESS['SALE_HPS_GPWEB_CURRENCY_DESC'] = 'ISO 4217 код валюты (978 - EUR)';
$MESS['SALE_HPS_GPWEB_CHANGE_STATUS_PAY'] = 'Автоматически менять статус заказа на оплачен';
$MESS['SALE_HPS_GPWEB_CHANGE_STATUS_PAY_DESC'] = 'При успешной оплате автоматически менять статус заказа';

// Сообщения об ошибках
$MESS['SALE_HPS_GPWEB_ERROR_CONFIG'] = 'Ошибка конфигурации обработчика платежной системы';
$MESS['SALE_HPS_GPWEB_ERROR_ORDER_NOT_FOUND'] = 'Заказ не найден';
$MESS['SALE_HPS_GPWEB_ERROR_SCHEMA_NOT_FOUND'] = 'Файл схемы GPwebpayAdditionalInfoRequest_v.5.xsd не найден';
$MESS['SALE_HPS_GPWEB_ERROR_PRIVATE_KEY_NOT_FOUND'] = 'Файл приватного ключа не найден';
$MESS['SALE_HPS_GPWEB_ERROR_PUBLIC_KEY_NOT_FOUND'] = 'Файл публичного ключа не найден';
$MESS['SALE_HPS_GPWEB_ERROR_RESPONSE'] = 'Не удалось проверить ответ платёжного шлюза. Статус оплаты не изменён.';

// Сообщения о статусах платежа
$MESS['SALE_HPS_GPWEB_PAYMENT_SUCCESS'] = 'Оплата произведена успешно';
$MESS['SALE_HPS_GPWEB_PAYMENT_ALREADY_PAID'] = 'Платеж уже был оплачен ранее';
$MESS['SALE_HPS_GPWEB_PAYMENT_STATUS_DISABLED'] = 'Автоматическое изменение статуса отключено';
$MESS['SALE_HPS_GPWEB_PAYMENT_ERROR'] = 'Ошибка при обработке платежа';

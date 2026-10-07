<?php
/**
 * Онлайн-отказ от договора: приём заявления с /odstupenie-od-zmluvy/
 *
 * Сохраняет заявление в журнал (инфоблок withdrawals) и отправляет событие
 * ODSTUPENIE_OD_ZMLUVY: письмо офису и подтверждение клиенту. Шаблоны и
 * журнал заводит local/tools/mail_odstupenie.php.
 *
 * Ничего не решает за офис: не подтверждает право на отказ и не возвращает
 * деньги - только принимает заявление и подтверждает получение.
 *
 * Ответ всегда JSON {success, message[, field]}: форма без ответа
 * зависает с выключенной кнопкой, и клиент не понимает, ушло ли заявление.
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

const WITHDRAWAL_IBLOCK_CODE = 'withdrawals';
const WITHDRAWAL_EVENT = 'ODSTUPENIE_OD_ZMLUVY';
const RECAPTCHA_MIN_SCORE = 0.5;

// Если проверка не прошла не по вине клиента, у него должен остаться
// способ отказаться от договора - VOP п. 6.4 разрешает e-mail
const FALLBACK_TEXT = ' Odstúpenie od zmluvy nám môžete poslať aj e-mailom na office@luxsol.sk.';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message, string $field = ''): void
{
    $result = ['success' => $success, 'message' => $message];
    if ($field !== '') {
        $result['field'] = $field;
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    die();
}

function postValue(string $name, int $maxLength): string
{
    $value = trim((string)($_POST[$name] ?? ''));
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

    return mb_substr($value, 0, $maxLength);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Nesprávna požiadavka.');
}

$order = postValue('order', 50);
$name = postValue('name', 150);
$mail = postValue('mail', 150);
$product = postValue('product', 2000);
$comment = postValue('comment', 3000);

if ($name === '') {
    respond(false, 'Zadajte meno a priezvisko.', 'name');
}
if ($mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Zadajte platný e-mail, pošleme naň potvrdenie.', 'mail');
}
if ($product === '') {
    respond(false, 'Uveďte tovar, ktorý chcete vrátiť.', 'product');
}

// ------------------------------------------------- reCAPTCHA
// Ключ лежит в settings.php: файл не выгружается на сервер
$secret = defined('RECAPTCHA_SECRET') ? RECAPTCHA_SECRET : '';

if ($secret === '') {
    AddMessage2Log('Отказ от договора не принят: в settings.php нет RECAPTCHA_SECRET', 'recaptcha');
    respond(false, 'Overenie zlyhalo, skúste to neskôr.' . FALLBACK_TEXT);
}

$http = new \Bitrix\Main\Web\HttpClient(['socketTimeout' => 10, 'streamTimeout' => 10]);
$answer = $http->post('https://www.google.com/recaptcha/api/siteverify', [
    'secret' => $secret,
    'response' => (string)($_POST['token'] ?? ''),
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
]);
$captcha = json_decode((string)$answer, true);

if (empty($captcha['success'])) {
    AddMessage2Log('Отказ от договора: reCAPTCHA не прошла: ' . ($answer ?: $http->getStatus()), 'recaptcha');
    respond(false, 'Overenie zlyhalo, obnovte stránku a skúste to znova.' . FALLBACK_TEXT);
}
if (($captcha['score'] ?? 0) < RECAPTCHA_MIN_SCORE) {
    AddMessage2Log('Отказ от договора: низкий балл reCAPTCHA ' . ($captcha['score'] ?? '?'), 'recaptcha');
    respond(false, 'Formulár sa nepodarilo overiť.' . FALLBACK_TEXT);
}

// ------------------------------------------------- журнал
// Сервер живёт в UTC, а дату получения клиент увидит в письме - и от неё
// отсчитываются сроки, поэтому время местное
$received = new \DateTime('now', new \DateTimeZone('Europe/Bratislava'));
$recordId = 0;

if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $iblock = \CIBlock::GetList([], ['CODE' => WITHDRAWAL_IBLOCK_CODE, 'CHECK_PERMISSIONS' => 'N'])->Fetch();

    if ($iblock) {
        $element = new \CIBlockElement();
        $recordId = (int)$element->Add([
            'IBLOCK_ID' => $iblock['ID'],
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'ORDER_NUMBER' => $order,
                'EMAIL' => $mail,
                'PRODUCT' => $product,
                'COMMENT' => $comment,
            ],
        ], false, false);

        if (!$recordId) {
            AddMessage2Log('Отказ от договора не записан в журнал: ' . $element->LAST_ERROR, 'withdrawal');
        }
    } else {
        AddMessage2Log('Нет инфоблока ' . WITHDRAWAL_IBLOCK_CODE . ', запустите local/tools/mail_odstupenie.php --apply', 'withdrawal');
    }
}

// ------------------------------------------------- письма
// Письмо - главное: без журнала заявление всё равно дойдёт до офиса
$sent = \CEvent::Send(WITHDRAWAL_EVENT, SITE_ID, [
    'ID' => $recordId ?: '—',
    'DATE' => $received->format('d.m.Y H:i'),
    // Номер заказа необязателен, как и в формуляре клиента («ak je známe»)
    'ORDER_NUMBER' => $order !== '' ? $order : 'neuvedené',
    'NAME' => $name,
    'EMAIL' => $mail,
    'PRODUCT' => $product,
    'COMMENT' => $comment !== '' ? $comment : '—',
], 'N');

if (!$sent) {
    AddMessage2Log('Отказ от договора: письмо не поставлено в очередь, запись #' . $recordId, 'withdrawal');

    // Заявление сохранено в журнале - офис его увидит, но клиенту
    // подтверждение не придёт, поэтому прямо об этом говорим
    if ($recordId) {
        respond(true, 'Vaše odstúpenie od zmluvy sme prijali (č. ' . $recordId . '). Potvrdenie e-mailom sa nepodarilo odoslať, v prípade potreby nám napíšte na office@luxsol.sk.');
    }
    respond(false, 'Odstúpenie sa nepodarilo odoslať.' . FALLBACK_TEXT);
}

respond(true, 'Ďakujeme, Vaše odstúpenie od zmluvy sme prijali. Potvrdenie o prijatí sme poslali na ' . $mail . '. O ďalšom postupe Vás budeme informovať e-mailom.');

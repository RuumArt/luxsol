<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if (!$name){ $arResult['success'] = false; $arResult['message'] = 'Meno - required'; echo json_encode($arResult); die(); }
if (!$phone){ $arResult['success'] = false; $arResult['message'] = 'Phone - required'; echo json_encode($arResult); die(); }
if (!$mail){ $arResult['success'] = false; $arResult['message'] = 'Mail - required'; echo json_encode($arResult); die(); }

$PROPS = [];
$PROPS['NAME'] = $name;
$PROPS['PHONE'] = $phone;
$PROPS['EMAIL'] = $mail;
$PROPS['MESSAGE'] = $message;
$arEventFields = $PROPS;

$url_google_api = 'https://www.google.com/recaptcha/api/siteverify';
// Ключ лежит в settings.php: файл не выгружается на сервер
$secret = defined('RECAPTCHA_SECRET') ? RECAPTCHA_SECRET : '';

if ($secret === '') {
    AddMessage2Log('Проверка формы не выполнена: в settings.php нет RECAPTCHA_SECRET', 'recaptcha');
    echo json_encode(['success' => false, 'message' => 'Overenie zlyhalo, skúste neskôr']);
    die();
}
$query = $url_google_api.'?secret='.$secret.'&response='.$_POST['token'].'&remoteip='.$_SERVER['REMOTE_ADDR'];
$data = json_decode(file_get_contents($query), true); // записываем полученные данные в виде ассоциативного массива
$score = $data['score'];

if($data['success']) {
	if($score >= 0.5) {
		if(CEvent::Send("CALL", SITE_ID, $arEventFields, "N", 48)){
			$arResult['success'] = true; 
			$arResult['message'] = 'Aplikácia bola úspešne odoslaná!';
			echo json_encode($arResult);
		}
	}else{
		$arResult['success'] = false; 
		$arResult['message'] = 'Spam!';
		echo json_encode($arResult);
	}
}

die();
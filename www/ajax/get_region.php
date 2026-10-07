<?php

/**
 * Подсказка населённого пункта по почтовому индексу
 *
 * Отвечает автодополнению в оформлении заказа: покупатель набирает PSČ,
 * получает список населённых пунктов с таким индексом.
 *
 * Источник - таблица room_sk_psc, наполняемая из официальных файлов
 * Slovenská pošta скриптом local/tools/import_psc.php
 *
 * Формат ответа сохранён прежним, чтобы не трогать фронт:
 *   {"status":"success","regions":[{"value":"98513","label":"Ábelová"}]}
 *
 * Разворачивается в /ajax/get_region.php
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Room\Services\PscService;

header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'error',
    'regions' => [],
    // Текст для покупателя, когда индекс набран полностью, но не найден
    'message' => '',
];

$term = (string)($_REQUEST['psc'] ?? '');
$digits = PscService::normalize($term);

if ($digits === '') {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    die();
}

// Пока справочник не залит, автодополнение молчит, но заказ оформляется
if (!PscService::isReady()) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    die();
}

$regions = PscService::suggest($digits);

if (!empty($regions)) {
    $response['status'] = 'success';
    $response['regions'] = $regions;

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    die();
}

// Совпадений нет. Пока индекс набран не до конца - это нормально,
// сообщать не о чем
if (strlen($digits) < 5) {
    $response['status'] = 'success';

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    die();
}

$response['status'] = 'success';

$response['message'] = PscService::isPoBox($digits)
    ? 'Toto PSČ patrí P.O. BOXu, kuriér naň nedoručuje. Zadajte prosím PSČ doručovacej adresy.'
    : 'Toto PSČ sme nenašli v zozname Slovenskej pošty. Skontrolujte ho prosím.';

echo json_encode($response, JSON_UNESCAPED_UNICODE);
die();

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Room\Services\CartService;
use Room\Tools\PriceCalculator;
use Room\Tools\CatalogSelection;

CModule::IncludeModule('catalog');
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'error' => '',
    'count' => 0,
    'data' => []
];

try {
    // Валидация входных данных
    $productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $quantity = isset($_POST['quant']) ? (int)$_POST['quant'] : 1;

    if ($productId <= 0) {
        throw new Exception('Не указан ID товара');
    }

    // Получение информации о товаре
    $baseProductId = CartService::getBaseProductId($productId);
    $productInfo = CartService::getProductInfo($baseProductId);

    if (empty($productInfo)) {
        throw new Exception('Товар не найден');
    }

    $selection = CatalogSelection::get($productId, (string)($_POST['color'] ?? ''));
    $articul = $selection['article'] !== '' ? $selection['article'] : $productInfo['article'];
    $color = $selection['color'];

    // Обработка товара с размерами
    if (!empty($_POST['sizes']) && is_array($_POST['sizes'])) {
        $sizes = $_POST['sizes'];
        $basePrice = isset($_POST['price']) ? (float)str_replace(",", ".", $_POST['price']) : 0;

        // Расчет цены
        $priceData = PriceCalculator::calculatePriceWithSizes([
            'sizes' => $sizes,
            'productId' => $productId,
            'baseProductId' => $baseProductId,
            'color' => $color,
            'basePrice' => $basePrice,
            'productProperties' => [
                'PROPERTY_S_POKRAS_VALUE' => $productInfo['paintSquareThreshold'],
                'PROPERTY_PRICE_POKRAS_VALUE' => $productInfo['paintGridPrice']
            ]
        ]);

        // Формирование свойств товара
        $properties = [
            ["NAME" => "Číslo výrobku", "VALUE" => $articul, "CODE" => "ARTICUL"],
            ["NAME" => "Farba", "VALUE" => $color, "CODE" => "COLOR"],
            ["NAME" => "Výška", "VALUE" => $priceData['height'], "CODE" => "HEIGHT"],
            ["NAME" => "Šírka", "VALUE" => $priceData['width'], "CODE" => "WIDTH"],
            ["NAME" => "Plocha", "VALUE" => $priceData['totalSquare'], "CODE" => "SQUARE"],
            ["NAME" => Loc::getMessage('ROOM_PROP_SIZES_TITLE'), "VALUE" => $priceData['sizesStr'], "CODE" => "SIZES_STR"],
            ["NAME" => "Цена", "VALUE" => $basePrice, "CODE" => "PRICE"],
            ["NAME" => "Цена без ндс", "VALUE" => $priceData['priceNovat'], "CODE" => "PRICE_NOVAT"]
        ];

        // Добавление в корзину
        CartService::removeFromBasketIfExists($productId);
        CartService::addProductWithSizes($productId, $priceData, $properties);

        $response['data'] = [
            'id' => $baseProductId,
            'price' => $priceData['price'],
            'name' => $productInfo['name'],
            'quantity' => 1
        ];
    } else {
        // Обработка простого товара
        $properties = [];
        if (!empty($articul)) {
            $properties[] = ["NAME" => "Číslo výrobku", "VALUE" => $articul, "CODE" => "ARTICUL"];
        }
        if (!empty($color)) {
            $properties[] = ["NAME" => "Farba", "VALUE" => $color, "CODE" => "COLOR"];
        }

        CartService::removeFromBasketIfExists($productId);
        $priceData = CartService::addSimpleProduct($productId, $quantity, $properties);

        $response['data'] = [
            'id' => $baseProductId,
            'price' => $priceData['price'],
            'name' => $productInfo['name'],
            'quantity' => $quantity
        ];
    }

    // Применение купона
    if (!empty($_POST['COUPON'])) {
        CartService::applyCoupon($_POST['COUPON']);
    }

    // Обновление корзины
    $basketData = CartService::refreshBasket();
    $response['count'] = $basketData['count'];
    $response['success'] = true;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
?>

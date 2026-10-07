<?php
/** CLI-only checks of conversion eligibility and the payment-return boundary. */
namespace Bitrix\Main {
    class Application { public static function getInstance() { return null; } }
}
namespace Bitrix\Sale\PaySystem {
    class Manager {
        public static function getById($id) { return ['ACTION_FILE' => [7 => 'gpweb', 2 => 'bill', 3 => 'cash'][$id] ?? '']; }
    }
}
namespace Bitrix\Sale {
    class Order {
        public static array $saved = [];
        public bool $paid = false;
        public bool $cancelled = false;
        public array $items;
        public function __construct(public int $id, public int $paySystem = 7) {
            $this->items = [new BasketItem()];
            self::$saved[$id] = $this;
        }
        public static function load($id) { return self::$saved[$id] ?? null; }
        public function getId() { return $this->id; }
        public function isPaid() { return $this->paid; }
        public function isCanceled() { return $this->cancelled; }
        public function getPaymentCollection() { return [new Payment($this->paySystem)]; }
        public function getBasket() { return $this->items; }
        public function getField($name) { return ['ACCOUNT_NUMBER' => 'ORDER-' . $this->id, 'TAX_VALUE' => 2.34][$name] ?? null; }
        public function getPrice() { return 43.90; }
        public function getDeliveryPrice() { return 7; }
        public function getCurrency() { return 'EUR'; }
    }
    class Payment {
        public function __construct(private int $id) {}
        public function getSum() { return 43.90; }
        public function getPaymentSystemId() { return $this->id; }
    }
    class BasketItem {
        public function getPropertyCollection() { return $this; }
        public function getPropertyValues() { return ['ARTICUL' => ['VALUE' => '470011']]; }
        public function getProductId() { return 740; }
        public function getField($name) { return 'Volejbalová sieť'; }
        public function getPrice() { return 18.45; }
        public function getQuantity() { return 2; }
    }
}
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
    require dirname(__DIR__, 2) . '/www/local/php_interface/room/classes/Tools/Ecommerce.php';
    use Room\Tools\Ecommerce;
    use Bitrix\Sale\Order;
    $_SESSION = [];
    $passed = 0;
    $check = static function (string $name, bool $ok) use (&$passed): void {
        if (!$ok) { throw new \RuntimeException('FAIL: ' . $name); }
        echo 'PASS: ', $name, PHP_EOL;
        $passed++;
    };

    $card = new Order(101);
    $check('unpaid card order produces no purchase', Ecommerce::purchasePayload($card) === null);
    $check('unpaid card is not marked sent', empty($_SESSION[Ecommerce::SESSION_KEY]));
    $check('opening the success URL alone produces no purchase', Ecommerce::paymentReturnPayload() === null);
    Ecommerce::rememberPaymentReturn(101);
    $check('callback followed by failed save produces no purchase', Ecommerce::paymentReturnPayload() === null);
    $card->paid = true;
    Ecommerce::rememberPaymentReturn(101);
    $payload = Ecommerce::paymentReturnPayload();
    $check('saved paid card produces purchase', ($payload['event'] ?? '') === 'purchase');
    $check('purchase contains actual order number and total', $payload['ecommerce']['transaction_id'] === 'ORDER-101' && $payload['ecommerce']['value'] === 43.90);
    $check('purchase contains currency, delivery and items', $payload['ecommerce']['currency'] === 'EUR' && $payload['ecommerce']['shipping'] === 7.0 && $payload['ecommerce']['items'][0]['quantity'] === 2.0 && $payload['ecommerce']['items'][0]['item_id'] === '470011');
    $check('success reload does not repeat purchase', Ecommerce::paymentReturnPayload() === null);
    Ecommerce::rememberPaymentReturn(101);
    $check('repeated return does not repeat purchase', Ecommerce::paymentReturnPayload() === null);
    $check('confirmation reload does not repeat purchase', Ecommerce::purchasePayload($card) === null);
    $cancelled = new Order(102); $cancelled->paid = true; $cancelled->cancelled = true;
    Ecommerce::rememberPaymentReturn(102);
    $check('cancelled order produces no purchase', Ecommerce::paymentReturnPayload() === null);
    Ecommerce::rememberPaymentReturn(999);
    $check('missing order produces no purchase', Ecommerce::paymentReturnPayload() === null);
    $check('bank transfer is counted on confirmation', Ecommerce::purchasePayload(new Order(103, 2)) !== null);
    $check('cash on delivery is counted on confirmation', Ecommerce::purchasePayload(new Order(104, 3)) !== null);
    $empty = new Order(105, 2); $empty->items = [];
    $check('empty basket produces no purchase', Ecommerce::purchasePayload($empty) === null);

    $checkout = Ecommerce::checkoutPayload([
        ['data' => ['PRODUCT_ID' => 740, 'NAME' => 'Sieť &amp; lano', 'PRICE' => 18.45, 'QUANTITY' => 2, 'PROPS' => [['CODE' => 'ARTICUL', 'VALUE' => '470011']]]],
        ['data' => ['PRODUCT_ID' => 741, 'NAME' => 'Lano', 'PRICE' => 5, 'QUANTITY' => 1]],
        ['data' => ['PRODUCT_ID' => 742, 'QUANTITY' => 0]],
    ], 'EUR');
    $check('checkout matches published GTM and GA4 payload', $checkout['event'] === 'checkout' && $checkout['ecommerce']['currency'] === 'EUR' && $checkout['ecommerce']['value'] === 41.90);
    $check('checkout preserves product IDs, quantities and decoded names', count($checkout['ecommerce']['items']) === 2 && $checkout['ecommerce']['items'][0]['item_id'] === '470011' && $checkout['ecommerce']['items'][0]['item_name'] === 'Sieť & lano' && $checkout['ecommerce']['items'][1]['item_id'] === '741');
    $check('empty checkout sends no event', Ecommerce::checkoutPayload([], 'EUR') === null);
    echo $passed, ' ecommerce checks passed; no real conversions or database writes.', PHP_EOL;
}

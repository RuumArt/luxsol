<?php
/** Render the real order-email function with fictional data; no Bitrix bootstrap or mail transport. */
namespace Bitrix\Main\Type { class DateTime extends \DateTime {} }
namespace Bitrix\Sale {
    class Order {
        public static $fixture;
        public static function load($id) { return self::$fixture; }
    }
}
namespace Bitrix\Catalog {
    class ProductTable {
        public static function getCurrentRatioWithMeasure($id) { return [$id => ['MEASURE' => ['SYMBOL_RUS' => 'ks']]]; }
    }
}
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
    $root = dirname(__DIR__, 2);
    require $root . '/www/local/php_interface/room/classes/Tools/Format.php';
    class CCatalogSku { public static function GetProductInfo($id) { return false; } }
    class CIBlockElement {
        public static function GetByID($id) { return new class { public function GetNext() { return false; } }; }
    }
    function getPropertyByCodeClear($props, $code) { return $props[$code] ?? ''; }
    function getPropertyByCode($props, $code) {
        return isset($props[$code]) ? new class($props[$code]) {
            public function __construct(private $value) {}
            public function getValue() { return $this->value; }
        } : null;
    }
    class MailPreviewItem {
        public function __construct(public array $props, public float $quantity = 1, public float $price = 10) {}
        public function getProductId() { return 123; }
        public function getField($name) { return 'Testovacia sieť'; }
        public function getPrice() { return $this->price; }
        public function getQuantity() { return $this->quantity; }
        public function getPropertyCollection() {
            return new class($this->props) {
                public function __construct(private array $props) {}
                public function getPropertyValues() { return $this->props; }
            };
        }
    }
    class MailPreviewOrder {
        public array $items = [];
        public string $number = '2843';
        public function getField($name) { return $name === 'ACCOUNT_NUMBER' ? $this->number : null; }
        public function getBasket() { return $this->items; }
        public function getPropertyCollection() {
            return ['FIO' => 'Ján', 'LAST_NAME' => 'Test', 'EMAIL' => 'buyer@example.test',
                'PHONE' => '421908201087', 'Adresa' => "Trnavského\r\n18", 'PSС' => '84101', 'Mesto' => 'Bratislava',
                'zas_add' => 'Testovací obchod', 'zas_place' => 'Bratislava'];
        }
        public function getShipmentCollection() { return []; }
        public function getPaymentCollection() { return []; }
        public function getDateInsert() { return new \Bitrix\Main\Type\DateTime('2026-10-29 12:00:00'); }
        public function getDeliveryPrice() { return 5; }
        public function getPrice() { return 15; }
    }
    $source = file_get_contents($root . '/www/local/php_interface/init.php');
    $start = strpos($source, 'function ModifyOrderSaleMailsNew(');
    $end = strpos($source, 'include_once("classes/handler/admin/OrderTabs.php")', $start);
    eval('use Bitrix\\Sale; use Room\\Tools\\Format;' . substr($source, $start, $end - $start));
    $passed = 0;
    $check = static function ($name, $ok) use (&$passed) {
        if (!$ok) { throw new \RuntimeException('FAIL: ' . $name); }
        $passed++;
        echo 'PASS: ', $name, PHP_EOL;
    };
    $order = new MailPreviewOrder();
    \Bitrix\Sale\Order::$fixture = $order;
    $render = static function () { $event = 'SALE_NEW_ORDER'; $fields = []; ModifyOrderSaleMailsNew(2843, $event, $fields); return $fields; };
    $order->items = [new MailPreviewItem(['SIZES_STR' => ['VALUE' => '1,3 × 0,7 m × 1 ks.'], 'SQUARE' => ['VALUE' => '0,9']])];
    $fields = $render();
    $check('order 2843 recovers 0.91 square metres from dimensions', str_contains($fields['EMAIL_PRODUCTS_TABLE'], 'Plocha: 0,91 m²'));
    $check('header confirms actual order number', str_contains($fields['EMAIL_HEADER'], 'Potvrdenie objednávky') && str_contains($fields['EMAIL_HEADER'], 'Vašu objednávku č. 2843 sme úspešne prijali.'));
    $check('number has no hash prefix', str_contains($fields['EMAIL_ORDER_NUMBER'], '>2843</span>') && !str_contains($fields['EMAIL_ORDER_NUMBER'], '#2843'));
    $check('header has no repeated thanks', !str_contains($fields['EMAIL_HEADER'], 'Ďakujeme'));
    $check('bottom has exact status and thanks copy', str_contains($fields['EMAIL_THANK_YOU_MESSAGE'], 'O ďalšom stave vašej objednávky Vás budeme informovať e-mailom a SMS.') && str_contains($fields['EMAIL_THANK_YOU_MESSAGE'], 'Ďakujeme za Vašu objednávku a dôveru.'));
    $check('Packeta pickup point has current label', str_contains($fields['EMAIL_DELIVERY_PAYMENT'], 'Výdajné miesto: Testovací obchod, Bratislava') && !str_contains($fields['EMAIL_DELIVERY_PAYMENT'], 'Zásielkovňa'));
    $check('phone and address formatting remain intact', str_contains($fields['EMAIL_CUSTOMER_INFO'], '+421 908 201 087') && str_contains($fields['EMAIL_CUSTOMER_INFO'], 'Trnavského 18, 841 01 Bratislava'));
    $check('numeric date remains intact', str_contains($fields['EMAIL_ORDER_NUMBER'], '29. 10. 2026, 12:00'));
    $order->items[0]->props = ['SIZES_STR' => ['VALUE' => '3x5mx1 ks.'], 'SQUARE' => ['VALUE' => '15']];
    $check('whole area retains two zeros', str_contains($render()['EMAIL_PRODUCTS_TABLE'], 'Plocha: 15,00 m²'));
    $order->items[0]->props = ['SIZES_STR' => ['VALUE' => "1.3x0.7mx2 ks.\n2.5x1mx1 ks."], 'SQUARE' => ['VALUE' => '4,3']];
    $multiple = $render()['EMAIL_PRODUCTS_TABLE'];
    $check('multiple cuts sum before final rounding', str_contains($multiple, 'Plocha: 4,32 m²'));
    $check('multiple sizes remain separate and quantities are not duplicated', str_contains($multiple, '1,3 × 0,7 m – 2 ks<br>2,5 × 1 m – 1 ks') && !str_contains($multiple, 'Množstvo:'));
    $order->items[0]->props = ['SQUARE' => ['VALUE' => '15,5']];
    $check('legacy order without cuts falls back to stored area', str_contains($render()['EMAIL_PRODUCTS_TABLE'], 'Plocha: 15,50 m²'));
    $order->items[0]->props = ['SIZES_STR' => ['VALUE' => '1x1mx1 ks.']];
    $check('cuts with missing area still display their area', str_contains($render()['EMAIL_PRODUCTS_TABLE'], 'Plocha: 1,00 m²'));
    $order->items = [new MailPreviewItem([], 2, 5)];
    $simple = $render()['EMAIL_PRODUCTS_TABLE'];
    $check('ordinary products keep quantity and total with no area', str_contains($simple, 'Množstvo: 2 ks') && str_contains($simple, '10,00 €') && !str_contains($simple, 'Plocha:'));
    $order->number = 'LUX-2843';
    $check('configured public order number is used consistently', str_contains($render()['EMAIL_HEADER'], 'LUX-2843') && str_contains($render()['EMAIL_ORDER_NUMBER'], '>LUX-2843</span>'));
    $order->number = '<2843>';
    $check('public order number is escaped', str_contains($render()['EMAIL_HEADER'], '&lt;2843&gt;') && !str_contains($render()['EMAIL_HEADER'], '<2843>'));
    echo $passed, " checks passed; no orders created or email sent.\n";
}

<?php
/** Regression for order 2838: a white SKU must never acquire a black basket label. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

class CIBlockElement
{
    public static array $elements = [];
    public static function GetByID($id) {
        return new class(self::$elements[$id] ?? null) {
            public function __construct(private ?array $row) {}
            public function Fetch() { return $this->row; }
            public function GetNextElement() {
                return $this->row ? new class($this->row) {
                    public function __construct(private array $row) {}
                    public function GetProperties() { return $this->row['PROPS']; }
                } : false;
            }
        };
    }
}

require dirname(__DIR__, 2) . '/www/local/php_interface/room/classes/Tools/CatalogSelection.php';
use Room\Tools\CatalogSelection;

CIBlockElement::$elements = [
    615 => ['PROPS'=>['ARTICLE'=>['VALUE'=>'420007'], 'COLOR'=>['VALUE'=>'307','PROPERTY_TYPE'=>'E','LINK_IBLOCK_ID'=>'5']]],
    20127 => ['PROPS'=>['ARTICLE'=>['VALUE'=>'420018'], 'COLOR'=>['VALUE'=>'312','PROPERTY_TYPE'=>'E','LINK_IBLOCK_ID'=>'5']]],
    100 => ['PROPS'=>['ARTICLE'=>['VALUE'=>'100110'], 'COLOR'=>['VALUE'=>'','PROPERTY_TYPE'=>'E','LINK_IBLOCK_ID'=>'5']]],
    307 => ['NAME'=>'Biela','IBLOCK_ID'=>'5'],
    312 => ['NAME'=>'Čierna','IBLOCK_ID'=>'5'],
];
$passed = 0;
$check = static function ($name, $ok) use (&$passed) {
    if (!$ok) { throw new RuntimeException('FAIL: ' . $name); }
    $passed++;
    echo 'PASS: ', $name, PHP_EOL;
};
$reject = static function ($name, $call) use ($check) {
    try { $call(); } catch (InvalidArgumentException | RuntimeException $error) {
        $check($name, true);
        return;
    }
    $check($name, false);
};
$check('white SKU provides white colour and its own article', CatalogSelection::get(615, 'Biela') === ['article'=>'420007','color'=>'Biela']);
$check('black SKU provides black colour and a different article', CatalogSelection::get(20127, 'Čierna') === ['article'=>'420018','color'=>'Čierna']);
$reject('order 2838 conflict is rejected', static fn() => CatalogSelection::get(615, 'Čierna'));
$reject('reverse conflict is also rejected', static fn() => CatalogSelection::get(20127, 'Biela'));
$check('missing browser colour uses black SKU, never default white', CatalogSelection::get(20127)['color'] === 'Čierna');
$check('undefined browser field uses actual SKU colour', CatalogSelection::get(20127, 'undefined')['color'] === 'Čierna');
$check('case and surrounding spaces do not create a false conflict', CatalogSelection::get(20127, ' čierna ')['color'] === 'Čierna');
$check('colourless accessory is not assigned browser default white', CatalogSelection::get(100, 'Biela') === ['article'=>'100110','color'=>'']);
$reject('unknown product is rejected', static fn() => CatalogSelection::get(999));
unset(CIBlockElement::$elements[307]);
$reject('broken colour link cannot silently accept browser value', static fn() => CatalogSelection::get(615, 'Biela'));
CIBlockElement::$elements[307] = ['NAME'=>'Biela','IBLOCK_ID'=>'1'];
$reject('colour reference must point into its colour dictionary', static fn() => CatalogSelection::get(615, 'Biela'));
echo $passed, " checks passed; no database or basket writes.\n";

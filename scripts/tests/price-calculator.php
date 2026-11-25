<?php
/** Real calculator with catalog fixtures. No Bitrix bootstrap, baskets or mail. */
class CCatalogProduct
{
    public static array $prices = [];
    public static function GetOptimalPrice($id, ...$args) { return self::$prices[$id] ?? false; }
}
class CIBlockPriceTools
{
    public static array $offers = [];
    public static function GetOffersArray($params, $ids, ...$args) { return self::$offers[$ids[0]] ?? []; }
}
class CIBlockElement
{
    public static function GetList($sort, $filter, ...$args) {
        return new class($filter['%NAME']) {
            public function __construct(private string $color) {}
            public function GetNext() { return ['ID'=>$this->color === 'Čierna' ? 312 : 307]; }
        };
    }
}
require dirname(__DIR__,2).'/www/local/php_interface/room/classes/Tools/Format.php';
require dirname(__DIR__,2).'/www/local/php_interface/room/classes/Tools/PriceCalculator.php';
use Room\Tools\PriceCalculator;
$passed=0;
$check=static function($name,$ok)use(&$passed){if(!$ok)throw new RuntimeException('FAIL: '.$name);$passed++;echo 'PASS: ',$name,PHP_EOL;};
$profile=static fn($net,$gross,$vat=0.23)=>['PRICE'=>['PRICE'=>$net,'VAT_INCLUDED'=>'N'],
    'RESULT_PRICE'=>['BASE_PRICE'=>$gross,'DISCOUNT_PRICE'=>$gross,'VAT_INCLUDED'=>'Y','VAT_RATE'=>$vat,'NO_VAT'=>$vat>0?'N':'Y']];
CCatalogProduct::$prices=[100=>$profile(4.2,5.17),101=>$profile(4.2,5.17),102=>$profile(6,7.38),200=>$profile(4.2,5.17)];
CIBlockPriceTools::$offers[100]=[['ID'=>101,'PROPERTY_COLOR_VALUE'=>307],['ID'=>102,'PROPERTY_COLOR_VALUE'=>312]];
$params=['productId'=>101,'baseProductId'=>100,'color'=>'Biela','basePrice'=>4.2,'productProperties'=>[],
    'sizes'=>[['height'=>'5,5','width'=>'3,7','count'=>6],['height'=>'5,5','width'=>'1,8','count'=>6]]];
$result=PriceCalculator::calculatePriceWithSizes($params);
$check('420111 screenshot totals 1045.84 gross instead of 849.62 net',$result['price']===1045.84);
$check('net amount belongs to the same gross calculation',$result['priceNovat']===850.28);
$check('area and piece count are preserved',$result['totalSquare']===181.5 && $result['countSum']===12);
foreach ([['4,99',2],['5',1.35],['9,99',1.35],['10',1.2],['19,99',1.2],['20',1]] as [$area,$coefficient]) {
    $test=array_replace($params,['sizes'=>[['height'=>$area,'width'=>1,'count'=>1]]]);
    $expected=round((float)str_replace(',','.',$area)*5.17*$coefficient,2);
    $check('area '.$area.' uses coefficient '.$coefficient,PriceCalculator::calculatePriceWithSizes($test)['price']===$expected);
}
$small=array_replace($params,['sizes'=>[['height'=>'5,5','width'=>'1,8','count'=>6]]]);
$check('markup uses each piece area even if total area exceeds twenty',PriceCalculator::calculatePriceWithSizes($small)['price']===414.58);
$spoofed=array_replace($params,['basePrice'=>0.01]);
$check('posted unit price cannot replace the server catalog price',PriceCalculator::calculatePriceWithSizes($spoofed)['price']===1045.84);
$plain=array_replace($params,['productId'=>200,'baseProductId'=>200,'basePrice'=>0.01]);
$check('product without offers also uses trusted gross unit price',PriceCalculator::calculatePriceWithSizes($plain)['price']===1045.84);
CCatalogProduct::$prices[200]=$profile(4.2,4.2,0);
$zero=PriceCalculator::calculatePriceWithSizes($plain);
$check('VAT-exempt product has identical gross and net amounts',$zero['price']===849.62 && $zero['priceNovat']===849.62);
CCatalogProduct::$prices[200]=$profile(5,6,0.2);
$rate=PriceCalculator::calculatePriceWithSizes($plain);
$check('net conversion uses catalog VAT rate rather than a hardcoded rate',$rate['price']===1213.74 && $rate['priceNovat']===1011.45);
CCatalogProduct::$prices[101]['RESULT_PRICE']['DISCOUNT_PRICE']=4.65;
$check('basket discounts are not pre-applied again by the calculator',PriceCalculator::calculatePriceWithSizes($params)['price']===1045.84);
CCatalogProduct::$prices[101]=$profile(4.2,5.17);
$paint=array_replace($params,['productId'=>102,'color'=>'Čierna','productProperties'=>['PROPERTY_S_POKRAS_VALUE'=>50,'PROPERTY_PRICE_POKRAS_VALUE'=>8]]);
$check('colour calculation uses gross prices while preserving paint formula',PriceCalculator::calculatePriceWithSizes($paint)['price']===1446.95);
CCatalogProduct::$prices[101]=false;
try { PriceCalculator::calculatePriceWithSizes($params); $check('missing catalog price fails closed',false); }
catch(RuntimeException $e) { $check('missing catalog price fails closed',true); }
echo $passed," checks passed; no catalog, basket or order writes.\n";

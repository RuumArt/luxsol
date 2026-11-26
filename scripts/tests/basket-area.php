<?php
/** Exact area in saved basket properties and checkout output; no database or orders. */
require dirname(__DIR__,2).'/www/local/php_interface/room/classes/Tools/Format.php';
require dirname(__DIR__,2).'/www/local/php_interface/room/classes/Services/CartService.php';
use Room\Tools\Format;
use Room\Services\CartService;
$passed=0;
$check=static function($name,$ok)use(&$passed){if(!$ok)throw new RuntimeException('FAIL: '.$name);$passed++;echo 'PASS: ',$name,PHP_EOL;};
$cuts='3,5 × 1,5 m × 3 ks.';
$check('legacy rounded area recovers 15.75 from dimensions',Format::area('15,8',$cuts,null)==='15,75');
$check('multiple cuts sum without rounding to tenths',Format::area('4,3',"1.3x0.7mx2 ks.\n2.5x1mx1 ks.",null)==='4,32');
$check('whole area has no padded zeroes in basket',Format::area('15','3x5mx1 ks.',null)==='15');
$check('square millimetres remain visible',Format::area('0,047','0.125x0.125mx3 ks.',null)==='0,046875');
$check('floating point tails are suppressed',Format::area('0.1','0.1x0.2mx3 ks.',null)==='0,06');
$check('legacy value without dimensions remains usable',Format::area('15,75','',null)==='15,75');
$check('email still uses exactly two decimal places',Format::area('15','3x5mx1 ks.')==='15,00');
$check('zero remains a visible number',Format::area(0,'',null)==='0');
$builder=new ReflectionMethod(CartService::class,'buildSizeProperties');
$saved=$builder->invoke(null,['price'=>109.93],[['CODE'=>'SQUARE','NAME'=>'Plocha','VALUE'=>15.75],['CODE'=>'SIZES_STR','NAME'=>'Rozmery','VALUE'=>$cuts]]);
$byCode=array_column($saved,'VALUE','CODE');
$check('saved basket area is 15.75, not 15.8',$byCode['SQUARE']==='15,75');
$check('area formatting never changes the calculated price',$byCode['CUSTOM_PRICE_PROP']===109.93);
$fine=$builder->invoke(null,['price'=>0.48],[['CODE'=>'SQUARE','NAME'=>'Plocha','VALUE'=>0.046875]]);
$check('persisted area retains more than three decimals',$fine[0]['VALUE']==='0,046875');

define('B_PROLOG_INCLUDED',true);
class AreaCheckoutComponent { public static function scaleImages(&$data,$scale) {} }
class AreaCheckoutHarness {
    public $__component;
    public function __construct() { $this->__component=new AreaCheckoutComponent(); }
    public function render(array &$result): void {
        $arResult=&$result;$arParams=['SERVICES_IMAGES_SCALING'=>'N'];
        require dirname(__DIR__,2).'/www/local/templates/luxol/components/bitrix/sale.order.ajax/order/result_modifier.php';
    }
}
$row=['data'=>['PRICE'=>109.93,'PROPS'=>[['CODE'=>'SQUARE','VALUE'=>'15,8'],['CODE'=>'SIZES_STR','VALUE'=>$cuts]]],'columns'=>['QUANTITY'=>3]];
$result=['JS_DATA'=>['GRID'=>['ROWS'=>[$row]]],'GRID'=>['ROWS'=>[$row]],'BASKET_ITEMS'=>[['PROPS'=>$row['data']['PROPS']]]];
(new AreaCheckoutHarness())->render($result);
foreach(['JS_DATA','GRID'] as $view){
    $r=$view==='JS_DATA'?$result['JS_DATA']['GRID']['ROWS'][0]:$result['GRID']['ROWS'][0];
    $props=array_column($r['data']['PROPS'],'VALUE','CODE');
    $check('checkout '.$view.' recovers exact area with units',$props['SQUARE']==='15,75 m²');
    $check('checkout '.$view.' keeps price',$r['data']['PRICE']===109.93);
}
$check('checkout basket item copy uses the same area',$result['BASKET_ITEMS'][0]['PROPS'][0]['VALUE']==='15,75 m²');
echo $passed," checks passed; no catalog, basket or order writes.\n";

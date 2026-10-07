<?
if (!CModule::IncludeModule("sale") || !CModule::IncludeModule("catalog"))
    return false;

use Bitrix\Sale;

IncludeModuleLangFile(__FILE__);
 
class CCatalogProductProviderCustom extends CCatalogProductProvider
{
    public static function GetProductData($arParams)
    {
        
        $arResult = parent::GetProductData($arParams);

        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
        $product = CPrice::GetByID($arResult['PRODUCT_PRICE_ID']);
        $basketItems = $basket->getBasketItems();

        $custom_price = 0;
        
        foreach ($basketItems as $basketItem) {
            if($basketItem->getProductId() == $product['PRODUCT_ID']){
                $propCollection = $basketItem->getPropertyCollection();
                foreach ($propCollection as $propertyItem) {
                    if($propertyItem->getField('CODE') == 'CUSTOM_PRICE_PROP'){
                        $custom_price = $propertyItem->getField('VALUE');
                    }
                    if($propertyItem->getField('CODE') == 'SQUARE'){
                        $square = $propertyItem->getField('VALUE');
                    }
                }
            }
        }

        $weight = $arResult['WEIGHT'];
        $dismensions_new = [];
        $dismensions = unserialize($arResult['DIMENSIONS']);
        $dismensions_new['WIDTH'] = $dismensions['WIDTH'] * $square;
        $dismensions_new['HEIGHT'] = $dismensions['HEIGHT'] * $square;
        $dismensions_new['LENGTH'] = $dismensions['LENGTH'] * $square;

        $arResult = [
            'BASE_PRICE' => $custom_price,
            'WEIGHT' => $square*$weight,
            //'DIMENSIONS' => serialize($dismensions_new),
        ] + $arResult;

        return $arResult;

    }
 
    public static function OrderProduct($arParams)
    {
        // code
    }
 
}
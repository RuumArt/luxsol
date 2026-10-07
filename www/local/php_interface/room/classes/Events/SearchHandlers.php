<?php

namespace Room\Events;

class SearchHandlers
{
    public static function productBeforeIndex($arFields)
    {
        if ($arFields['MODULE_ID'] == 'iblock' && $arFields['PARAM2'] == IBLOCK_ID__CATALOG && strlen($arFields['ITEM_ID']) > 0) {

            \Bitrix\Main\Loader::includeModule('iblock');

            $res = \CIBlockElement::GetByID($arFields['ITEM_ID']);
            $element = $res->GetNext();

            if (isset($element) && is_array($element)) {

                \Bitrix\Main\Loader::includeModule('sale');

                $allProductPrices = \Bitrix\Catalog\PriceTable::getList([
                    "select" => ["*"],
                    "filter" => [
                        "=PRODUCT_ID" => $element['ID'],
                    ],
                ]);

                $sum_price = 0;

                while($arPrice = $allProductPrices->fetch())
                {
                    $sum_price += $arPrice['PRICE'];
                }

                $productData = \Bitrix\Catalog\ProductTable::getList([
                    'filter' => ['=ID' => $element['ID']],
                    'select' => [
                        'ID',
                        'QUANTITY',
                        'QUANTITY_RESERVED',
                        'CAN_BUY_ZERO',
                        'AVAILABLE'
                    ]
                ])->fetch();

                if (empty($sum_price) || $productData["AVAILABLE"] == "N" || empty($element['PREVIEW_PICTURE'])) {
                    $arFields['BODY'] = '';
                    $arFields['TITLE'] = '';
                    $arFields['TAGS'] = '';
                }
            }
        }

        return $arFields;
    }
}

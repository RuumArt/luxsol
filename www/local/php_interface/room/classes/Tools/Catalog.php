<?php

namespace Room\Tools;

class Catalog
{
    /* Получение массива размеров */
    public static function getSizeOffersArray($item)
    {
        $sizes = [];

        $counter = 0;

        if ($item['HAVE_OFFERS']) {
            foreach ($item["OFFERS"] as $offer) {
                $size = $offer["PROPERTIES"]["ATT_SIZE"];

                if (!empty($size["VALUE"])) {
                    $sizes[] = [
                        "ID" => $offer["ID"],
                        "NAME" => $offer["NAME"],
                        "TITLE" => $size["VALUE"],
                    ];

                    $counter++;
                }
            }

            usort($sizes, function($a, $b) {
                return $a['TITLE'] - $b['TITLE'];
            });
        }

        if (!empty($sizes)) {
            $sizes[0]['SELECTED'] = true;
        }

        return $sizes;
    }

    /* Получение цены "До скидки" */
    public static function getBeforeDiscountPrice($item, $price = 0)
    {
        $oldPrice = $price;

        if (empty($oldPrice)) {
            $hasOffers = !empty($item["OFFERS"]);
            $currentItem = $hasOffers ? $item["OFFERS"][0] : $item;
            $prices = $currentItem["ITEM_PRICES"];

            $oldPrice = array_shift($prices)["RATIO_PRICE"];
        }

        $rsPrice = \Bitrix\Catalog\Model\Price::getList(array(
            'filter' => ['CATALOG_GROUP.XML_ID' => SALE_PRICE_XML_ID, 'PRODUCT_ID' => $currentItem["ID"]],
        ));

        if ($arPrice = $rsPrice->fetch()) {
            if((float) $arPrice["PRICE"] === (float) $oldPrice) {
                return 0;
            }

            return CurrencyFormat($arPrice["PRICE"], $arPrice["CURRENCY"]);
        }
    }
}


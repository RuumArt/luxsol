<?php

namespace Room\Events;

class IblockHandlers
{
    // Идентификатор ИБ новостей
    const IB_CATALOG = IBLOCK_ID__CATALOG;
    const IB_CATALOG_TP = IBLOCK_ID__CATALOG_TP;
    const IB_COLLECTION = IBLOCK_ID__COLLECTION;
    const ARTICUL_PROP_ID = CATALOG_ARTICUL_PROP_ID;
    const COLLECTION_PROPS_CODE = ["ATT_COLLECTION_1", "ATT_COLLECTION_2", "ATT_COLLECTION_3"];
    const COLLECTION_REAL_CODE = "ATT_COLLECTION_REAL";

    /**
     * Метод копирует значения и добавляет новое множественное поле коллекций
     *
     * @param $arFields
     * @return int
     */
    protected static function createPropFields($iblockIds = [], $VALUES): int
    {
        $arPropFields = [
            "NAME" => "Коллекция",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => self::COLLECTION_REAL_CODE,
            "MULTIPLE" => "Y",
            "PROPERTY_TYPE" => "L",
            "VALUES" => $VALUES,
        ];

        foreach ($iblockIds as $iblockId) {

            $res = \CIBlock::GetProperties($iblockId, [], [ "CODE" => self::COLLECTION_REAL_CODE ]);

            $property_ib = new \CIBlockProperty;

            if ($res_arr = $res->Fetch()) {
                $realCollectionId = $res_arr["ID"];

                $db_enum_list = \CIBlockProperty::GetPropertyEnum($realCollectionId, ['SORT' => 'ASC']);

                $sort = 0;

                while($ar_enum = $db_enum_list->Fetch())
                {
                    $current_values[$ar_enum['VALUE']] = [ "ID" => $ar_enum['ID'], "VALUE" => $ar_enum['VALUE'] ];
                }

                $newValues = [];

                foreach ($VALUES as $item) {
                    $sort++;

                    if (!empty($current_values[$item["VALUE"]])) {
                        $newValues[$current_values[$item["VALUE"]]["ID"]] = [
                            "SORT" => $sort,
                            "VALUE" => $current_values[$item["VALUE"]]["VALUE"],
                        ];
                    } else {
                        $newValues[] = [
                            "SORT" => $sort, "VALUE" => $item["VALUE"],
                        ];
                    }
                }

                $property_ib->UpdateEnum($realCollectionId, $newValues);
            } else {
                $arPropFields["IBLOCK_ID"] = $iblockId;
                $arPropFields["DEFAULT_VALUE"] = "";

                $realCollectionId = $property_ib->Add($arPropFields);
            }
        }

        return (int) $realCollectionId;
    }

    /**
     * Обработка события изменения свойств инфоблока
     *
     * @param $arFields
     * @return bool
     */
    public static function onUpdateCatalogProperty(&$arFields): bool
    {
        if ($arFields["CODE"] == self::COLLECTION_REAL_CODE) {
            unset($arFields["VALUES"]);
        }

        if ($arFields["CODE"] == self::COLLECTION_PROPS_CODE[0])
        {
            /* Получаем значения свойства "Коллекция 1" */

            $VALUES = $arFields["VALUES"];

            /* Выходим если пусто */

            if (empty($VALUES)) return true;

            /* Добавляем или обновляем свойство инфоблока */

            self::createPropFields([self::IB_CATALOG, self::IB_COLLECTION], $VALUES);
        }

        return true;
    }

    public static function generateSymbolCodeForSections(&$arFields) {
        $params = array(
            "max_len" => "100",
            "replace_space" => "-",
            "replace_other" => "-",
            "delete_repeat_replace" => "true",
            "use_google" => "false",
        );

        if (strlen($arFields["NAME"]) > 0 && (in_array($arFields["IBLOCK_ID"], [self::IB_CATALOG]))) {
            $arFields['CODE'] = \CUtil::translit($arFields["NAME"], "ru", $params);
        }
    }

    public static function generateSymbolCodeForElements(&$arFields) {
        $params = array(
            "max_len" => "100",
            "replace_space" => "-",
            "replace_other" => "-",
            "delete_repeat_replace" => "true",
            "use_google" => "false",
        );

        if (strlen($arFields["NAME"]) > 0 && (in_array($arFields["IBLOCK_ID"], [self::IB_CATALOG]))) {
            $props = $arFields["PROPERTY_VALUES"];
            $articulProp = array_shift($props[self::ARTICUL_PROP_ID]);

            $codeName = $arFields["NAME"];

            if(!empty($articulProp["VALUE"])) {
                $codeName .= ' '.$articulProp["VALUE"];
            }

            $arFields['CODE'] = \CUtil::translit($codeName, "ru", $params);
        }
    }

    /**
     * Метод проставляет признака "Sale" у товара при наличии скидочной цены
     *
     * @param $arFields
     * @return bool
     */
    public static function onUpdateProductSale(&$arFields): bool
    {
        if (!in_array($arFields['IBLOCK_ID'], [self::IB_CATALOG, self::IB_CATALOG_TP])) {
            return true;
        }

        AddMessage2Log('Изменение инфоблока верх: '. $arFields["ID"]);

        $isSale = false;

        $productId = $arFields["ID"];

        if ($arFields['IBLOCK_ID'] == self::IB_CATALOG_TP) {
            $mxResult = \CCatalogSku::GetProductInfo(
                $arFields["ID"]
            );

            $productId = $mxResult['ID'];
        }

        $res = \CCatalogSKU::getOffersList([$productId], self::IB_CATALOG, [], ['ID']);

        if (empty($res[$productId])) {
            return true;
        }

        $mainOffer = array_shift($res[$productId]);

        if (!empty($mainOffer)) {
            $rsPrice = \Bitrix\Catalog\Model\Price::getList(array(
                'filter'=> [
                    'CATALOG_GROUP.ID' => [MAIN_PRICE_ID, SALE_PRICE_ID],
                    'PRODUCT_ID' => $mainOffer["ID"]
                ],
            ));

            $prices = [];

            while ($arPrice = $rsPrice->fetch()) {
                $prices[$arPrice["CATALOG_GROUP_ID"]] = $arPrice;
            }

            if (!empty($prices[SALE_PRICE_ID])) {
                if ((float) $prices[SALE_PRICE_ID]["PRICE"] !== (float) $prices[MAIN_PRICE_ID]["PRICE"]) {
                    $isSale = true;
                }
            }

            $db_enum_list = \CIBlockProperty::GetPropertyEnum("ATT_IS_SALE", [], ["IBLOCK_ID" => self::IB_CATALOG]);
            $arrProp = [];

            while($ar_enum_list = $db_enum_list->GetNext()) {
                $arrProp[] = $ar_enum_list;
            }

            $currentEnumKey = array_search($isSale ? 'Да' : 'Нет', array_column($arrProp, "VALUE"));

            \CIBlockElement::SetPropertyValuesEx($productId, self::IB_CATALOG, [ 'ATT_IS_SALE' => $arrProp[$currentEnumKey]["ID"] ]);
        }

        return true;
    }

    /**
     * Метод собирает и проставляет значения в множественное поле коллекций
     *
     * @param $arFields
     * @return bool
     */
    public static function onUpdateCollectionsAfterUpdate(&$arFields): bool
    {
        // Выходим, если изменяет не товар

        if ($arFields['IBLOCK_ID'] != self::IB_CATALOG) {
            return true;
        }

        /* Получаем значение свойств инфоблока */

        $PROP_VALUES = [];

        foreach (self::COLLECTION_PROPS_CODE as $propCode) {
            $rsProps = \CIBlockElement::GetProperty(self::IB_CATALOG, $arFields["ID"], [], ["CODE" => $propCode]);

            while ($arrProps = $rsProps->Fetch()) {
                $PROP_VALUES[$arrProps["VALUE_ENUM"]] = $arrProps["VALUE_ENUM"];
            }
        }

        $db_enum_list = \CIBlockProperty::GetPropertyEnum(self::COLLECTION_REAL_CODE, [], ["IBLOCK_ID" => self::IB_CATALOG]);

        $VALUES = [];

        while ($ar_enum_list = $db_enum_list->GetNext())
        {
            if (in_array($ar_enum_list["VALUE"], $PROP_VALUES)) {
                $VALUES[$ar_enum_list["ID"]] = $ar_enum_list["ID"];
            }
        }

        if (empty($VALUES)) return true;

        \CIBlockElement::SetPropertyValuesEx($arFields["ID"], self::IB_CATALOG, [ self::COLLECTION_REAL_CODE => $VALUES ]);

        return true;
    }
}

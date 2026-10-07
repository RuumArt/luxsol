<?php
/**
 * Created by olegpro.ru
 * User: Oleg Maksimenko <oleg.39style@gmail.com>
 */
 
namespace Olegpro\Classes\Handlers\Search;
 
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
 
class Stemming
{
 
    /**
     * @var array
     */
    protected static $allowedIblockId = array(
        1
    );
 
    /**
     * @var
     */
    private static $element;
 
    /**
     * @param array $arFields
     * @return mixed
     */
    public static function beforeIndex($arFields)
    {
        static $allowedIblockId = null;
 
        if ($allowedIblockId === null) {
            $allowedIblockId = array_flip(self::$allowedIblockId);
        }
 
        if (
            $arFields['MODULE_ID'] == 'iblock'
            && isset($allowedIblockId[$arFields['PARAM2']])
            && strlen($arFields['ITEM_ID']) > 0
            && substr($arFields['ITEM_ID'], 0, 1) != 'S'
            && Loader::includeModule('iblock')
        ) {
            if ($obElement = \CIBlockElement::GetList(
                array(),
                array('ID' => $arFields['ITEM_ID']),
                false,
                false,
                array()
            )->GetNextElement()
            ) {
 
                $element = $obElement->GetFields();
 
                $element['PROPERTIES'] = $obElement->GetProperties();
 
                self::$element = $element;
 
            }
 
        }
 
        return $arFields;
 
    }
 
 
    /**
     * Events: OnBeforeIndexUpdate and OnAfterIndexAdd
     * @param $indexId
     * @param $arFields
     */
    public static function beforeIndexUpdate($indexId, $arFields)
    {
 
        if (
            isset(self::$element) && is_array(self::$element)
            && isset(self::$element['PROPERTIES']['ARTICLE'])
            && strlen(trim(self::$element['PROPERTIES']['ARTICLE']['VALUE']))
            && preg_match('~^.*?([0-9.,]+).*?$~', trim(self::$element['PROPERTIES']['ARTICLE']['VALUE']), $m)
        ) {
 
            $word = ToUpper($m[1]);
 
            $stemId = \CSearch::RegisterStem($word);
 
            if ($stemId > 0) {
 
                $connection = Application::getConnection();
 
                $sqlHelper = $connection->getSqlHelper();
 
                try {
 
                    $thereIs = $connection->queryScalar(
                        sprintf(
                            "SELECT 1 FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = '%s' AND STEM = '%s'",
                            $sqlHelper->forSql($indexId),
                            \CSearch::RegisterStem($word)
                        )
                    );
 
                    if ($thereIs === null) {
                        $connection->query(sprintf(
                            "INSERT IGNORE INTO `b_search_content_stem` (`SEARCH_CONTENT_ID`, `LANGUAGE_ID`, `STEM`, `TF`, `PS`) VALUES ('%s', '%s', '%s', '%s', '%s')",
                            $sqlHelper->forSql($indexId),
                            'ru',
                            \CSearch::RegisterStem($word),
                            number_format(0.2, 4, ".", ""),
                            number_format(1, 4, ".", "")
                        ));
                    }
 
                } catch (SqlQueryException $e) {
                    AddMessage2Log(sprintf("\\%s:\n%s", __METHOD__, $e->getMessage()));
                }
 
            }
 
        }
 
        self::$element = null;
 
    }
 
}
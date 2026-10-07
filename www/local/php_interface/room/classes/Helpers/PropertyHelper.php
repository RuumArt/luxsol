<?php

namespace Room\Helpers;

/**
 * Класс для работы со свойствами заказов Bitrix
 */
class PropertyHelper
{
    /**
     * Получает объект свойства по коду
     * 
     * @param \Bitrix\Sale\PropertyValueCollection $propertyCollection Коллекция свойств
     * @param string $code Код свойства
     * @return \Bitrix\Sale\PropertyValue|null Объект свойства или null, если не найдено
     */
    public static function getPropertyByCode($propertyCollection, string $code)
    {
        foreach ($propertyCollection as $property) {
            if ($property->getField('CODE') == $code) {
                return $property;
            }
        }
        
        return null;
    }

    /**
     * Получает значение свойства по коду
     * 
     * @param \Bitrix\Sale\PropertyValueCollection $propertyCollection Коллекция свойств
     * @param string $code Код свойства
     * @return string Значение свойства или пустая строка, если не найдено
     */
    public static function getPropertyByCodeClear($propertyCollection, string $code): string
    {
        $property = self::getPropertyByCode($propertyCollection, $code);
        
        if ($property) {
            $value = $property->getValue();
            return $value !== null ? (string)$value : '';
        }
        
        return '';
    }
}

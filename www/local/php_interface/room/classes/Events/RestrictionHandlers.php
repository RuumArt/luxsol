<?php

namespace Room\Events;

class RestrictionHandlers
{
    /**
     * Ограничения для доставок
     * @return \Bitrix\Main\EventResult
     */
    public static function onCheckRuleDelivery()
    {
        $fileDelivery = '/local/php_interface/room/classes/Events/restrictions/delivery.php';

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\checkDeliveryBrand' => $fileDelivery,     // по группе пользователя
                // Toptrans показываем, только если заказ не разложить по мешкам
                '\Room\Delivery\OversizeRestriction' =>
                    '/local/php_interface/room/classes/Delivery/OversizeRestriction.php',
            )
        );
    }
}
?>
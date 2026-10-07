<?php

namespace Room\Events;

class DeliveryHandlers
{
    /**
     * Путь к папке с обработчиками от корня сайта
     */
    const CLASS_DIR = '/local/php_interface/room/classes/Delivery/';

    /**
     * Регистрация своих служб доставки
     *
     * Битрикс ждёт карту "класс => файл": автозагрузчик проекта ему
     * не указ, путь нужно назвать явно
     *
     * @return \Bitrix\Main\EventResult
     */
    public static function onBuildHandlersList()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                '\Room\Delivery\DpdHandler' => self::CLASS_DIR . 'DpdHandler.php',
                '\Room\Delivery\PacketaHandler' => self::CLASS_DIR . 'PacketaHandler.php',
                '\Room\Delivery\ToptransHandler' => self::CLASS_DIR . 'ToptransHandler.php',
            ]
        );
    }
}

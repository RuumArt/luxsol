<?php

namespace Room\Delivery;

use Bitrix\Sale\Delivery\Restrictions\Base;
use Bitrix\Sale\Internals\Entity;

/**
 * Ограничение по неразложимому заказу
 *
 * Заказ неразложим, если в нём есть отдельный кусок тяжелее мешка курьера
 * (30 кг). У ограничения два режима:
 *
 * - "только неразложимый" - для Toptrans: поддоны нужны только тогда,
 *   когда курьер не справится, в обычной корзине их не показываем;
 * - "только разложимый" - для DPD: курьер такой заказ не повезёт.
 *
 * Почему DPD нужно именно ограничение, а не только ошибка обработчика:
 * sale.order.ajax считает ошибку расчёта доставки предупреждением. Служба
 * остаётся в списке, её можно выбрать и оформить заказ - доставка при этом
 * не посчитана, а отгрузка лишь помечается как проблемная. Ограничение
 * убирает службу из списка совсем
 */
class OversizeRestriction extends Base
{
    const MODE_ONLY_OVERSIZE = 'ONLY_OVERSIZE';
    const MODE_NO_OVERSIZE = 'NO_OVERSIZE';

    public static $easeSort = 200;

    public static function getClassTitle()
    {
        return 'по неразложимому заказу (кусок тяжелее 30 кг)';
    }

    public static function getClassDescription()
    {
        return 'Показывает доставку только для заказов, которые курьер не повезёт,'
            . ' или наоборот - только для тех, которые повезёт.';
    }

    public static function getParamsStructure($entityId = 0)
    {
        return [
            'MODE' => [
                'TYPE' => 'ENUM',
                'LABEL' => 'Показывать доставку',
                'DEFAULT' => self::MODE_ONLY_OVERSIZE,
                'OPTIONS' => [
                    self::MODE_ONLY_OVERSIZE => 'только если в заказе есть кусок тяжелее 30 кг',
                    self::MODE_NO_OVERSIZE => 'только если все куски не тяжелее 30 кг',
                ],
            ],
        ];
    }

    /**
     * @param Entity $entity
     * @return array|bool
     */
    protected static function extractParams(Entity $entity)
    {
        if (!$entity instanceof \Bitrix\Sale\Shipment) {
            return false;
        }

        return Packer::piecesFromShipment($entity);
    }

    /**
     * @param mixed $pieces веса кусков из extractParams
     * @param array $restrictionParams
     * @param int $deliveryId
     * @return bool
     */
    public static function check($pieces, array $restrictionParams, $deliveryId = 0)
    {
        // Ограничение, созданное до появления режимов, работало для Toptrans
        $mode = $restrictionParams['MODE'] ?? self::MODE_ONLY_OVERSIZE;

        // Корзину ещё не собрали - не мешаем показу доставки
        if (!is_array($pieces) || empty($pieces)) {
            return true;
        }

        $oversize = false;

        foreach ($pieces as $piece) {
            if ($piece > DpdHandler::MAX_BAG_WEIGHT + 0.000001) {
                $oversize = true;
                break;
            }
        }

        return $mode === self::MODE_NO_OVERSIZE ? !$oversize : $oversize;
    }
}

<?php

namespace Room\Delivery;

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

/**
 * Toptrans: доставка на поддонах
 *
 * Нужна там, где курьером уже нельзя: в заказе есть отдельный кусок сетки
 * тяжелее предела DPD, который не разложить по мешкам.
 *
 * Цены перевозчика уже с НДС, как у DPD, - пересчитывать не нужно.
 * У Packeta наоборот, там налог добавляется.
 *
 * Ставка и минимум правятся в админке, в настройках службы
 */
class ToptransHandler extends Base
{
    /**
     * Цена за килограмм, евро с НДС
     */
    const PRICE_PER_KG = 1.0;

    /**
     * Меньше этой суммы доставка не стоит: до 50 кг всегда 50 евро.
     * Тоже с НДС
     */
    const MIN_PRICE = 50.0;

    public static function getClassTitle()
    {
        return 'Toptrans, поддоны';
    }

    public static function getClassDescription()
    {
        return 'Считает доставку как цену за килограмм, но не меньше минимальной суммы. Цены с НДС.';
    }

    /**
     * Поля, которые видит менеджер в настройках службы доставки
     *
     * @return array
     */
    protected function getConfigStructure()
    {
        return [
            'MAIN' => [
                'TITLE' => 'Тариф Toptrans',
                'DESCRIPTION' => 'Цены с НДС, как их выставляет перевозчик.',
                'ITEMS' => [
                    'PRICE_PER_KG' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Цена за килограмм, €',
                        'DEFAULT' => self::PRICE_PER_KG,
                    ],
                    'MIN_PRICE' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Минимальная стоимость доставки, €',
                        'DEFAULT' => self::MIN_PRICE,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Shipment $shipment
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new CalculationResult();

        $config = is_array($this->config['MAIN'] ?? null) ? $this->config['MAIN'] : [];

        $perKg = Tariff::number($config, 'PRICE_PER_KG', self::PRICE_PER_KG);
        $minPrice = Tariff::number($config, 'MIN_PRICE', self::MIN_PRICE);

        $weight = 0.0;

        foreach (Packer::piecesFromShipment($shipment) as $piece) {
            $weight += $piece;
        }

        $result->setDeliveryPrice(round(max($minPrice, $weight * $perKg), 2));

        return $result;
    }

    /**
     * Цена по весу заказа
     *
     * Оставлена для проверочных скриптов: объекта службы у них нет,
     * настройки читаются напрямую
     *
     * @param float $weightKg
     * @return float
     */
    public static function priceForWeight(float $weightKg): float
    {
        $config = Tariff::configOf(self::class);

        $perKg = Tariff::number($config, 'PRICE_PER_KG', self::PRICE_PER_KG);
        $minPrice = Tariff::number($config, 'MIN_PRICE', self::MIN_PRICE);

        return round(max($minPrice, $weightKg * $perKg), 2);
    }

    public function isCalculatePriceImmediately()
    {
        return true;
    }
}

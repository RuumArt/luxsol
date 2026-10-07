<?php

namespace Room\Delivery;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

/**
 * Packeta: выдача в пункте, тариф по весу
 *
 * Всегда одна посылка, заказ не делится. Предел 10 кг: пунктов, которые
 * принимают до 15 кг, мало, и тяжёлую посылку неудобно нести. Всё, что
 * тяжелее, везёт курьер DPD
 *
 * Шкала, предел веса и ставка НДС правятся в админке, в настройках службы.
 * Константы ниже - значения по умолчанию, пока поля не заполнены
 */
class PacketaHandler extends Base
{
    /**
     * Предельный вес заказа, кг
     */
    const MAX_WEIGHT = 10.0;

    /**
     * Ставка НДС в процентах: перевозчик выставляет тариф без налога
     */
    const VAT_PERCENT = 23.0;

    /**
     * Тариф: верхняя граница веса в кг => цена в евро БЕЗ НДС
     */
    const TARIFF = [
        [5.0, 2.30],
        [10.0, 3.80],
        [15.0, 4.30],
    ];

    public static function getClassTitle()
    {
        return 'Packeta, тариф по весу';
    }

    public static function getClassDescription()
    {
        return 'Считает доставку по весу заказа. Одна посылка.'
            . ' Тариф перевозчика без НДС, налог добавляется.';
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
                'TITLE' => 'Тариф Packeta',
                'DESCRIPTION' => 'Цены БЕЗ НДС, как в прайсе перевозчика. Налог добавляется при расчёте.',
                'ITEMS' => [
                    'TARIFF' => [
                        'TYPE' => 'STRING',
                        'NAME' => 'Шкала без НДС: вес до = цена, через точку с запятой',
                        'DEFAULT' => Tariff::format(self::TARIFF),
                    ],
                    'MAX_WEIGHT' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Предельный вес заказа, кг',
                        'DEFAULT' => self::MAX_WEIGHT,
                    ],
                    'VAT_PERCENT' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'НДС, %',
                        'DEFAULT' => self::VAT_PERCENT,
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

        $maxWeight = Tariff::number($config, 'MAX_WEIGHT', self::MAX_WEIGHT);

        $weight = 0.0;

        foreach (Packer::piecesFromShipment($shipment) as $piece) {
            $weight += $piece;
        }

        if ($weight > $maxWeight + 0.000001) {
            $result->addError(new Error(
                'Packeta doručuje zásielky do ' . $maxWeight . ' kg.'
            ));

            return $result;
        }

        $rows = Tariff::parse($config['TARIFF'] ?? '', self::TARIFF);
        $vat = Tariff::number($config, 'VAT_PERCENT', self::VAT_PERCENT);

        $result->setDeliveryPrice(round(Tariff::priceFor($rows, $weight) * (1 + $vat / 100), 2));

        return $result;
    }

    /**
     * Цена с НДС по весу заказа
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

        $rows = Tariff::parse($config['TARIFF'] ?? '', self::TARIFF);
        $vat = Tariff::number($config, 'VAT_PERCENT', self::VAT_PERCENT);

        return round(Tariff::priceFor($rows, $weightKg) * (1 + $vat / 100), 2);
    }

    public function isCalculatePriceImmediately()
    {
        return true;
    }
}

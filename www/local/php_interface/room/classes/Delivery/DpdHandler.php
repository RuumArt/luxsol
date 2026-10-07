<?php

namespace Room\Delivery;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;

/**
 * DPD: курьерская доставка по Словакии с тарифом по весу
 *
 * Заказ раскладывается по мешкам, цена - сумма тарифов всех мешков.
 * Предел мешка 30 кг, а не паспортные 31,5: на сайте виден вес сетки
 * без коробки и упаковки, плюс погрешность весов
 *
 * Шкала и предел мешка правятся в админке, в настройках службы.
 * Константы ниже - значения по умолчанию, пока поля не заполнены
 */
class DpdHandler extends Base
{
    /**
     * Предельный вес одного мешка, кг
     */
    const MAX_BAG_WEIGHT = 30.0;

    /**
     * Цена, если ни у одного товара в заказе не указан вес, евро с НДС.
     * Без неё такой заказ считался бесплатным: упаковщик не собирает
     * посылок из кусков нулевого веса, а сумма по пустому списку - ноль.
     * 7 евро выбрал клиент - это цена самой лёгкой посылки
     */
    const UNKNOWN_WEIGHT_PRICE = 7.0;

    /**
     * Тариф: верхняя граница веса в кг => цена в евро с НДС
     */
    const TARIFF = [
        [3.0, 7.0],
        [5.0, 8.0],
        [10.0, 9.0],
        [20.0, 11.0],
        [31.5, 15.0],
    ];

    public static function getClassTitle()
    {
        return 'DPD, тариф по весу';
    }

    public static function getClassDescription()
    {
        return 'Считает доставку по весу. Заказ тяжелее предела мешка раскладывается'
            . ' на несколько посылок, цена - сумма тарифов по каждой.';
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
                'TITLE' => 'Тариф DPD',
                'DESCRIPTION' => 'Цены с НДС, как их выставляет перевозчик.',
                'ITEMS' => [
                    'TARIFF' => [
                        'TYPE' => 'STRING',
                        'NAME' => 'Шкала: вес до = цена, через точку с запятой',
                        'DEFAULT' => Tariff::format(self::TARIFF),
                    ],
                    'MAX_BAG_WEIGHT' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Предельный вес одной посылки, кг',
                        'DEFAULT' => self::MAX_BAG_WEIGHT,
                    ],
                    'UNKNOWN_WEIGHT_PRICE' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Цена, если вес товаров не указан, €',
                        'DEFAULT' => self::UNKNOWN_WEIGHT_PRICE,
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

        $rows = Tariff::parse($config['TARIFF'] ?? '', self::TARIFF);
        $maxBag = Tariff::number($config, 'MAX_BAG_WEIGHT', self::MAX_BAG_WEIGHT);

        $pieces = Packer::piecesFromShipment($shipment);

        $packed = Packer::pack($pieces, $maxBag, static function (float $weightKg) use ($rows) {
            return Tariff::priceFor($rows, $weightKg);
        });

        // Один кусок тяжелее предела: разложить нельзя, нужен Toptrans
        if ($packed === null) {
            $result->addError(new Error(
                'Zásielka obsahuje kus ťažší ako ' . $maxBag
                . ' kg, kuriérom ju poslať nemožno.'
            ));

            return $result;
        }

        // Вес не указан ни у одного товара: посылок нет, но доставка не бесплатная
        if (empty($packed['bags'])) {
            $result->setDeliveryPrice(
                Tariff::number($config, 'UNKNOWN_WEIGHT_PRICE', self::UNKNOWN_WEIGHT_PRICE)
            );

            return $result;
        }

        $bagCount = count($packed['bags']);

        $result->setDeliveryPrice($packed['price']);

        if ($bagCount > 1) {
            $result->setPacksCount($bagCount);
            $result->setDescription('Zásielka bude rozdelená na ' . $bagCount . ' balíky.');
        }

        return $result;
    }

    /**
     * Цена одного мешка по его весу
     *
     * Вызывается и со стороны, без объекта службы: так считают стоимость
     * при сборке накладной DPD и в проверочных скриптах. Поэтому шкала
     * берётся из настроек службы напрямую
     *
     * @param float $weightKg
     * @return float
     */
    public static function priceForBag(float $weightKg): float
    {
        return Tariff::priceFor(self::tariffRows(), $weightKg);
    }

    /**
     * Действующая шкала: из настроек службы, иначе из константы
     *
     * @return array
     */
    public static function tariffRows(): array
    {
        static $rows = null;

        if ($rows === null) {
            $config = Tariff::configOf(self::class);
            $rows = Tariff::parse($config['TARIFF'] ?? '', self::TARIFF);
        }

        return $rows;
    }

    /**
     * Действующий предел веса посылки
     *
     * @return float
     */
    public static function maxBagWeight(): float
    {
        static $value = null;

        if ($value === null) {
            $value = Tariff::number(Tariff::configOf(self::class), 'MAX_BAG_WEIGHT', self::MAX_BAG_WEIGHT);
        }

        return $value;
    }

    public function isCalculatePriceImmediately()
    {
        return true;
    }
}

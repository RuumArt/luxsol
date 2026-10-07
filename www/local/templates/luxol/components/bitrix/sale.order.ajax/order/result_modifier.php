<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);

/**
 * Технические свойства позиции корзины, которые покупателю показывать не нужно
 *
 * HEIGHT и WIDTH - суммы по всем кускам, реальные размеры лежат в SIZES_STR.
 * PRICE, PRICE_NOVAT, CUSTOM_PRICE_PROP участвуют только в расчёте цены.
 *
 * Фильтруем по CODE, а не по названию: названия переводятся и меняются,
 * коды остаются прежними
 */
$hiddenPropCodes = [
	'HEIGHT',            // Výška / Высота - сумма по всем кускам
	'WIDTH',             // Šírka / Ширина - сумма по всем кускам
	'PRICE',             // Цена
	'PRICE_NOVAT',       // Цена без ндс
	'CUSTOM_PRICE_PROP', // Рассчитанная цена
	'SIZES',             // Dimenze - старое свойство, дублирует SIZES_STR
	'CATALOG.XML_ID',
	'PRODUCT.XML_ID',
	'SUM_OF_CHARGE',
];

/**
 * У товара с размерами количество позиции всегда 1: реальные количества
 * указаны по каждому куску в SIZES_STR. Показывать "1 ks" бессмысленно,
 * поэтому подменяем колонку количества на пустое значение
 *
 * Пустая строка не подходит: JS считает её отсутствием колонки
 * и подставляет сырое data.QUANTITY
 */
$hasSizes = static function (array $props): bool {
	foreach ($props as $prop) {
		if (($prop['CODE'] ?? '') === 'SIZES_STR' && !empty($prop['VALUE'])) {
			return true;
		}
	}

	return false;
};

$clearQuantity = static function (array &$row) use ($hasSizes): void {
	if (empty($row['data']['PROPS']) || !$hasSizes($row['data']['PROPS'])) {
		return;
	}

	$row['columns']['QUANTITY'] = '&nbsp;';
};

/**
 * Площадь хранится числом, единицу дописываем при выводе.
 * Компонент экранирует значения свойств, поэтому не html, а символ
 */
$addUnits = static function (array $props): array {
	foreach ($props as &$prop) {
		if (($prop['CODE'] ?? '') === 'SQUARE' && ($prop['VALUE'] ?? '') !== '') {
			$prop['VALUE'] = $prop['VALUE'] . ' m²';
		}
	}

	unset($prop);

	return $props;
};

$filterProps = static function (array $props) use ($hiddenPropCodes, $addUnits): array {
	return $addUnits(array_values(array_filter(
		$props,
		static function ($prop) use ($hiddenPropCodes) {
			return empty($prop['CODE']) || !in_array($prop['CODE'], $hiddenPropCodes, true);
		}
	)));
};

// Данные для JS: из них рисуется список товаров на шаге оформления
if (!empty($arResult['JS_DATA']['GRID']['ROWS'])) {
	foreach ($arResult['JS_DATA']['GRID']['ROWS'] as &$jsRow) {
		$clearQuantity($jsRow);

		if (!empty($jsRow['data']['PROPS'])) {
			$jsRow['data']['PROPS'] = $filterProps($jsRow['data']['PROPS']);
		}
	}

	unset($jsRow);
}

// Тот же список, отрисованный сервером
if (!empty($arResult['GRID']['ROWS'])) {
	foreach ($arResult['GRID']['ROWS'] as &$row) {
		$clearQuantity($row);

		if (!empty($row['data']['PROPS'])) {
			$row['data']['PROPS'] = $filterProps($row['data']['PROPS']);
		}
	}

	unset($row);
}

if (!empty($arResult['BASKET_ITEMS'])) {
	foreach ($arResult['BASKET_ITEMS'] as &$basketItem) {
		if (!empty($basketItem['PROPS'])) {
			$basketItem['PROPS'] = $filterProps($basketItem['PROPS']);
		}
	}

	unset($basketItem);
}

/*
 * Наценка за способ оплаты убирается из цены доставки в обработчике
 * Room\Events\SaleHandlers::hideDeliveryMarkup - на событии
 * OnSaleComponentOrderDeliveriesCalculated.
 *
 * Здесь этого делать нельзя: result_modifier не выполняется при
 * ajax-пересчёте заказа, компонент отдаёт JS_DATA напрямую
 */

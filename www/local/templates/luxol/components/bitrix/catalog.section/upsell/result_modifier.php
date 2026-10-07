<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

/**
 * Порядок карточек = порядок значений свойства ITEM_UPSELL
 *
 * Подписи из UPSELL_TITLES шаблон раздаёт по порядковому номеру вывода,
 * а компонент сортирует товары по своим правилам (SORT, затем ID desc).
 * Из-за этого подпись могла оказаться не у того товара. Выстраиваем
 * товары в том порядке, в котором их перечислил менеджер в карточке
 */
if (!empty($arParams['UPSELL_IDS']) && is_array($arParams['UPSELL_IDS']) && !empty($arResult['ITEMS'])) {
    $order = array_flip(array_values(array_map('intval', $arParams['UPSELL_IDS'])));

    usort($arResult['ITEMS'], static function ($a, $b) use ($order) {
        $posA = $order[(int)$a['ID']] ?? PHP_INT_MAX;
        $posB = $order[(int)$b['ID']] ?? PHP_INT_MAX;

        return $posA <=> $posB;
    });
}

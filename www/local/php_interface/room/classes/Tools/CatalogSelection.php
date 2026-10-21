<?php

namespace Room\Tools;

/** Article and colour belong to the selected catalog element, not to POST fields. */
class CatalogSelection
{
    public static function get(int $productId, string $requestedColor = ''): array
    {
        $element = \CIBlockElement::GetByID($productId)->GetNextElement();
        if (!$element) {
            throw new \InvalidArgumentException('Vybraný tovar už nie je dostupný. Obnovte stránku.');
        }
        $props = $element->GetProperties();
        $color = '';
        $colorValue = $props['COLOR']['VALUE'] ?? '';
        if (is_array($colorValue) && $colorValue !== []) {
            throw new \InvalidArgumentException('Vyberte konkrétny farebný variant tovaru.');
        }
        if ($colorValue === []) {
            $colorValue = '';
        }
        if ($colorValue !== '' && $colorValue !== null) {
            if (($props['COLOR']['PROPERTY_TYPE'] ?? '') === 'E') {
                $linkedColor = \CIBlockElement::GetByID((int)$colorValue)->Fetch();
                if (!$linkedColor || (int)$linkedColor['IBLOCK_ID'] !== (int)$props['COLOR']['LINK_IBLOCK_ID']) {
                    throw new \RuntimeException('Farbu tovaru sa nepodarilo overiť. Obnovte stránku.');
                }
                $color = trim((string)$linkedColor['NAME']);
            } else {
                $color = trim((string)($props['COLOR']['VALUE_ENUM'] ?? $colorValue));
            }
        }

        $requestedColor = trim($requestedColor);
        if ($color !== '' && $requestedColor !== '' && $requestedColor !== 'undefined'
            && mb_strtolower($color) !== mb_strtolower($requestedColor)) {
            // Do not silently choose a colour: either value could be the stale one.
            throw new \InvalidArgumentException('Farba sa nezhoduje s vybraným variantom. Obnovte stránku a vyberte farbu znova.');
        }

        return [
            'article' => (string)($props['ARTICLE']['VALUE'] ?? $props['ARTICUL']['VALUE'] ?? ''),
            'color' => $color,
        ];
    }
}

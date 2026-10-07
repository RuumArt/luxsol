<?php

use Bitrix\Main\Localization\Loc;

/**
 * Разметка строки размеров - одна на весь сайт
 *
 * Используется в трёх местах:
 *  - карточка товара (catalog.element/element) - видимая первая строка;
 *  - корзина (sale.basket.basket/new) - строки уже добавленных размеров;
 *  - заготовки для JS, из которых all.js собирает новую строку по кнопке "+".
 *
 * {{INDEX}} - плейсхолдер номера строки, JS подставляет туда число
 */

/**
 * Колонки одной строки размеров
 *
 * @param string $inputStyle Инлайновый стиль полей ввода
 * @return string
 */
function room_sizes_item_inner(string $inputStyle = ''): string
{
    $style = $inputStyle !== '' ? ' style="' . $inputStyle . '"' : '';

    ob_start();
    ?>
    <div class="sizes__item-inner">
        <div class="sizes__item-col">
            <div class="sizes__item-title"><?=Loc::getMessage('ROOM_SIZE_HEIGHT')?></div>
            <div class="sizes__item-input">
                <input type="text" name="sizes[{{INDEX}}][height]"<?=$style?>>
            </div>
        </div>
        <div class="sizes__item-col">
            <div class="sizes__item-title"><?=Loc::getMessage('ROOM_SIZE_WIDTH')?></div>
            <div class="sizes__item-input">
                <input type="text" name="sizes[{{INDEX}}][width]"<?=$style?>>
            </div>
        </div>
        <div class="sizes__item-col">
            <div class="sizes__item-title"><?=Loc::getMessage('ROOM_SIZE_COUNT')?></div>
            <div class="sizes__item-input">
                <input type="text" name="sizes[{{INDEX}}][count]"<?=$style?>>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}

/**
 * Кнопка "+" с подписью
 *
 * @return string
 */
function room_sizes_item_plus(): string
{
    return '<button class="sizes__item-plus">+</button>'
        . '<span class="sizes__item-plus-label">' . Loc::getMessage('ROOM_SIZE_ADD') . '</span>';
}

/**
 * Кнопка "-"
 *
 * @return string
 */
function room_sizes_item_minus(): string
{
    return '<button class="sizes__item-minus">-</button>';
}

/**
 * Заготовки строк для JS: первая (с "+") и добавляемая (с "-")
 * Нужны на каждой странице, где работает кнопка "+"
 *
 * @param string $inputStyle Инлайновый стиль полей ввода
 * @return void
 */
function room_sizes_item_templates(string $inputStyle = ''): void
{
    $inner = room_sizes_item_inner($inputStyle);
    ?>
    <script type="text/html" id="sizes-item-first-template">
        <div class="sizes__item"><?=$inner?><?=room_sizes_item_plus()?></div>
    </script>
    <script type="text/html" id="sizes-item-template">
        <div class="sizes__item"><?=$inner?><?=room_sizes_item_minus()?></div>
    </script>
    <?php
}

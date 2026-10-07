<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

$prodId = $_POST['id'];


if(!empty($prodId)):

$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

$basketItems = $basket->getBasketItems();
$sizes = [];

foreach ($basket as $basketItem) {
    if($basketItem->getProductId() == $prodId){
        $sizes = (array)($_SESSION['cart_sizes'][$prodId] ?? []);
    }
}

$counter = 0;

foreach ($sizes as $key => $size) : ?>

<div class="sizes__item">
	<div class="sizes__item-inner">
		<div class="sizes__item-col">
			<div class="sizes__item-title">
                <?=Loc::getMessage('ROOM_SIZE_HEIGHT')?>
			</div>
			<div class="sizes__item-input">
				<input type="text" name="sizes[<?=$key?>][height]" style="width: 85px;" im-insert="true" value="<?=$size['height']?>">
			</div>
		</div>
		<div class="sizes__item-col">
			<div class="sizes__item-title">
                <?=Loc::getMessage('ROOM_SIZE_WIDTH')?>
			</div>
			<div class="sizes__item-input">
				<input type="text" name="sizes[<?=$key?>][width]" style="width: 85px;" im-insert="true" value="<?=$size['width']?>">
			</div>
		</div>
		<div class="sizes__item-col">
			<div class="sizes__item-title">
				<?=Loc::getMessage('ROOM_SIZE_COUNT')?>
			</div>
			<div class="sizes__item-input">
				<input type="text" name="sizes[<?=$key?>][count]" style="width: 85px;" im-insert="true" value="<?=$size['count']?>">
			</div>
		</div>
	</div>
    <? if($counter == 0): ?>
	    <button class="sizes__item-plus">+</button>
	    <span class="sizes__item-plus-label"><?=Loc::getMessage('ROOM_SIZE_ADD')?></span>
    <? else: ?>
        <button class="sizes__item-minus">-</button>
    <? endif; ?>
</div>

<? $counter++; endforeach; endif; ?>
<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc,
	Bitrix\Sale,
	Room\Helpers\PropertyHelper,
	Room\Tools\Format;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}

/**
 * ID картинки товара. У торгового предложения берём картинку основного товара.
 * Замыкание, а не функция: шаблон может подключаться дважды за хит
 *
 * @var callable $getPreviewPictureId
 */
$getPreviewPictureId = static function ($productId) {
	$parent = CCatalogSKU::GetProductInfo($productId);

	if (!empty($parent["ID"])) {
		$productId = $parent["ID"];
	}

	$res = CIBlockElement::GetList(
		[],
		["IBLOCK_ID" => 1, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "ID" => $productId],
		false,
		false,
		["ID", "PREVIEW_PICTURE"]
	);

	$element = $res->Fetch();

	return !empty($element["PREVIEW_PICTURE"]) ? $element["PREVIEW_PICTURE"] : false;
};

?>
<div class="order-result">
<? if (!empty($arResult["ORDER"])): 
	$order = Sale\Order::load($arResult['ORDER']['ID']);
	$propertyCollection = $order->getPropertyCollection();
	$main_name = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'FIO');
	$main_last_name = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'LAST_NAME');
	$main_address = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Adresa');
	$main_psc = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PSС');
	$main_mesto = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Mesto');
	$main_phone = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'PHONE');
	$main_email = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'EMAIL');

	$main_firma = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'Firma');
	$main_ico = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'IČO');
	$main_icdph = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'IČ DPH');
	$main_dic = PropertyHelper::getPropertyByCodeClear($propertyCollection, 'DIČ');

	// Плюс дорисовываем только к непустому номеру, иначе в вёрстке остаётся одинокий "+"
	if ($main_phone !== '') {
		$main_phone = '+' . ltrim($main_phone, '+');
	}
	
	$isPaid = $order->isPaid(); 

	// Неоплаченная карта не создаёт purchase. Переход на оплату остаётся немедленным.
	$roomPaymentUrl = '';
?>

	<div class="order-result__head"><?=Loc::getMessage("SOA_ORDER_SUC", array(
					"#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y \o H:i'),
					"#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
				))?></div>

	<div class="order-result__payer">
		<div class="order-result__title">
			Fakturačná adresa
		</div>
		<div class="order-payer">
			<? if(!empty($main_name)): ?>
				<div class="order-payer__item">
					<span>Krstné meno:</span> <?=$main_name?>
				</div>
			<? endif; ?>
			<? if(!empty($main_last_name)): ?>
				<div class="order-payer__item">
					<span>Priezvisko:</span> <?=$main_last_name?>
				</div>
			<? endif; ?>
			<? if(!empty($main_address)): ?>
				<div class="order-payer__item">
					<span>Adresa:</span> <?=$main_address?>
				</div>
			<? endif; ?>
			<? if(!empty($main_psc)): ?>
				<div class="order-payer__item">
					<span>PSČ:</span> <?=$main_psc?>
				</div>
			<? endif; ?>
			<? if(!empty($main_mesto)): ?>
				<div class="order-payer__item">
					<span>Mesto:</span> <?=$main_mesto?>
				</div>
			<? endif; ?>
			<? if(!empty($main_phone)): ?>
				<div class="order-payer__item">
					<span>Telefón:</span> <?=$main_phone?>
				</div>
			<? endif; ?>
			<? if(!empty($main_email)): ?>
				<div class="order-payer__item">
					<span>E-mail:</span> <?=$main_email?>
				</div>
			<? endif; ?>
			<? if(!empty($main_firma)): ?>
				<div class="order-payer__item">
					<span>Spoločnosť:</span> <?=$main_firma?>
				</div>
			<? endif; ?>
			<? if(!empty($main_ico)): ?>
				<div class="order-payer__item">
					<span>IČO:</span> <?=$main_ico?>
				</div>
			<? endif; ?>
			<? if(!empty($main_icdph)): ?>
				<div class="order-payer__item">
					<span>IČ DPH:</span> <?=$main_icdph?>
				</div>
			<? endif; ?>
			<? if(!empty($main_dic)): ?>
				<div class="order-payer__item">
					<span>DIČ:</span> <?=$main_dic?>
				</div>
			<? endif; ?>
		</div>
	</div>

	
	<?
		$basket = $order->getBasket();
	?>
	<div class="order-result__info">
	<? if(!empty($arResult["ORDER"]['DELIVERY_ID'])): ?>
		<? 
			$delivery = \Bitrix\Sale\Delivery\Services\Manager::getById($arResult['ORDER']['DELIVERY_ID']); 
			$delivery_name = $delivery['NAME'];
			$delivery_description = $delivery['DESCRIPTION'];
			$delivery_logo = $delivery['LOGOTIP'];
			$delivery_price = Format::price($order->getDeliveryPrice());
		?>

		<div class="order-result__info-item">
			<div class="order-result__title">
				<?=Loc::getMessage("SOA_DELIVERY") ?>
			</div>
			<div class="ps-card">
				<div class="ps-card__logo">
					<?=CFile::ShowImage($delivery['LOGOTIP'], 200, 200, "border=0\"", "", false) ?>
				</div>
				<div class="ps-card__name"><?=$delivery_name ?> (<?=$delivery_price ?>)</div>
			</div>
		</div>
		
	<? endif; ?>

	<?
	//pre($arResult['ORDER']['PRICE']);
	if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
	{
		if (!empty($arResult["PAYMENT"]))
		{
			foreach ($arResult["PAYMENT"] as $payment)
			{
				if ($payment["PAID"] != 'Y')
				{
					if (!empty($arResult['PAY_SYSTEM_LIST'])
						&& array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
					)
					{
						$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

						if (empty($arPaySystem["ERROR"]))
						{
							?>

							<div class="order-result__info-item">
								<div class="order-result__title">
									<?=Loc::getMessage("SOA_PAY") ?>
								</div>
								<div class="ps-card">
									<div class="ps-card__logo">
										<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 200, 200, "border=0\"", "", false) ?>
									</div>
									<div class="ps-card__name"><?=$arPaySystem["NAME"] ?></div>
									<div class="ps-card__action">
										<? if (!$isPaid && strlen($arPaySystem["ACTION_FILE"]) > 0 && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
											<?
											$orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
											$paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
											?>
											<?
											if ($roomPaymentUrl === '') {
												$roomPaymentUrl = $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . $orderAccountNumber . "&PAYMENT_ID=" . $paymentAccountNumber;
											}
											?>
										<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
										<? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
										<br/>
											<? //=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
										<? endif ?>
										<? else: ?>
											<? //=$arPaySystem["BUFFERED_OUTPUT"]?>
										<? endif ?>
									</div>
								</div>
							</div>

							<?
						}
						else
						{
							?>
							<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
							<?
						}
					}
					else
					{
						?>
						<span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
						<?
					}
				}
			}
		}
	}
	else
	{
		?>
		<strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
		<?
	}
	?>
<? else: /*?>

	<b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
	<br /><br />

	<table class="sale_order_full_table">
		<tr>
			<td>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
				<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
			</td>
		</tr>
	</table>

<? */ endif ?>
</div>

<div class="order-result__products">
	<div class="order-result__title"><?=Loc::getMessage('ROOM_ORDER_CONTENT')?>:</div>
	
	<div class="products">
	<?
	foreach ($basket as $basketItem)
	{
		$productId = $basketItem->getProductId();
		$props = $basketItem->getPropertyCollection()->getPropertyValues();

		$arMeasure = Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($productId);
		$measureSymbol = $arMeasure[$productId]['MEASURE']['SYMBOL_RUS'] ?: Loc::getMessage('ROOM_ORDER_PIECES');

		$src_image = CFile::ResizeImageGet(
			$getPreviewPictureId($productId),
			array("width" => 300, "height" => 300),
			BX_RESIZE_IMAGE_PROPORTIONAL
		);

		// Каждый размер отдельной строкой: "2 × 2 m — 1 ks"
		$sizeLines = !empty($props['SIZES_STR']['VALUE'])
			? Format::sizeLines($props['SIZES_STR']['VALUE'])
			: [];

		$hasSizes = !empty($sizeLines);

		// У товара с размерами количество всегда 1, а цена уже посчитана за все
		// куски. У обычного товара показываем количество и умножаем на цену
		$itemQuantity = $hasSizes
			? ''
			: Format::number($basketItem->getQuantity()) . ' ' . $measureSymbol;

		$itemTotal = Format::price(
			$hasSizes ? $basketItem->getPrice() : $basketItem->getPrice() * $basketItem->getQuantity()
		);

		?>
			<div class="products__item">
				<div class="products-card">
					<div class="products-card__image">
						<? if(!empty($src_image)): ?>
								<img src="<?=$src_image['src']?>" alt="">
						<? endif; ?>
					</div>
					<div class="products-card__info">
						<div class="products-card__title"><?=htmlspecialcharsbx($basketItem->getField('NAME'))?></div>

						<? if ($hasSizes): ?>
							<div class="products-card__sizes">
								<? foreach ($sizeLines as $sizeLine): ?>
									<div class="products-card__size"><?=htmlspecialcharsbx($sizeLine)?></div>
								<? endforeach; ?>
							</div>
						<? endif; ?>

						<div class="products-card__props">
							<? if (!empty($props['COLOR']['VALUE'])): ?>
								<div class="products-card__prop">
									<span><?=Loc::getMessage('ROOM_PROP_COLOR')?>:</span> <?=htmlspecialcharsbx($props['COLOR']['VALUE'])?>
								</div>
							<? endif; ?>
							<? if (!empty($props['SQUARE']['VALUE'])): ?>
								<div class="products-card__prop">
									<span><?=Loc::getMessage('ROOM_PROP_SQUARE')?>:</span> <?=Format::number($props['SQUARE']['VALUE'])?> m<sup>2</sup>
								</div>
							<? endif; ?>
							<? if ($itemQuantity !== ''): ?>
								<div class="products-card__prop">
									<span><?=Loc::getMessage('ROOM_SIZE_COUNT')?>:</span> <?=htmlspecialcharsbx($itemQuantity)?>
								</div>
							<? endif; ?>
						</div>
					</div>
					<div class="products-card__price"><?=$itemTotal?></div>
				</div>
			</div>
		<?
	}
	?>
	</div>
</div>

<div class="order-result__totals">
	<?=Loc::getMessage('ROOM_ORDER_TOTAL')?>: <span><?=Format::price($arResult['ORDER']['PRICE'])?></span>
</div>
<?
// Перевод и оплата при получении учитываются при оформлении.
// Для GP WebPay purchasePayload вернёт null, пока заказ не оплачен.
$roomPurchase = !empty($order) ? \Room\Tools\Ecommerce::purchasePayload($order) : null;
require $_SERVER['DOCUMENT_ROOT'] . '/local/templates/luxol/include/purchase.php';
?>
</div>

<div class="back-main">
	<div class="back-main__title"><?=Loc::getMessage('ROOM_ORDER_THANKS')?></div>
	<div class="back-main__buttons">
		<div class="back-main__button">
			<a href="/catalog/" class="btn btn-success"><?=Loc::getMessage('ROOM_ORDER_CONTINUE')?></a>
		</div>
	</div>
</div>

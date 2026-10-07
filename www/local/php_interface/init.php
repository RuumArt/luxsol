<?

require_once __DIR__ . '/room/bootstrap.php';




@require_once 'providers/product_provider_custom.php';

use Bitrix\Main\Loader;
use Bitrix\Sale;
use Room\Services;
use Room\Helpers\PropertyHelper;
use Room\Tools\Format;

Loader::IncludeModule('highloadblock');
Loader::includeModule('sale');
Loader::includeModule('catalog');

/* function custom_mail($to, $subject, $message, $additional_headers = '', $additional_parameters = '')
{
    $additional_parameters = '--account=luxsol.sk --from=office@luxsol.sk';

    $result = @mail( $to , $subject , $message , $additional_headers , $additional_parameters );

    return $result;
}*/

function my_crop($text, $length, $clearTags = true)
{
    $text = trim($text);
    if ($clearTags === true)
        $text = strip_tags($text);
    if ($length <= 0 || strlen($text) <= $length)
        return $text;
    $out = mb_substr($text, 0, $length);
    $pos = mb_strrpos($out, ' ');
    if ($pos)
        $out = mb_substr($out, 0, $pos);
    return $out.'…';
}
function numberEnd($number, $titles) {
    $cases = array (2, 0, 1, 1, 1, 2);
    return $titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
}

AddEventHandler("iblock", "OnAfterIBlockElementAdd", "MyOnAfterIBlockElementAdd");

function MyOnAfterIBlockElementAdd($arFields){
    if($arFields['IBLOCK_ID'] == 3){
        $arSend = array(
            'NAME' => $arFields['NAME'],
            'PHONE' => $arFields['PROPERTY_VALUES'][8],
            'TEXT' => $arFields['PREVIEW_TEXT'],
        );
        $mail = CEvent::Send('CALL','s1',$arSend);
    }
}

/* Получаем свойство по коду */
function getPropertyByCode($propertyCollection, $code) {
    return PropertyHelper::getPropertyByCode($propertyCollection, $code);
}

function getPropertyByCodeClear($propertyCollection, $code) {
    return PropertyHelper::getPropertyByCodeClear($propertyCollection, $code);
}

AddEventHandler("sale", "OnOrderNewSendEmail", "ModifyOrderSaleMailsNew");

/**
 * Аналог ModifyOrderSaleMails на основе шаблона order_notification_sk.html.
 * Тело письма начинается с <!-- Header -->. Поддерживает 2 опциональных набора контактов:
 * основной (FIO, LAST_NAME, Adresa, Mesto, PSС, EMAIL, PHONE + Firma, IČO...) и доставка на другую адресу (_2).
 */
function ModifyOrderSaleMailsNew($orderID, &$eventName, &$arFields)
{
    $order = Sale\Order::load($orderID);
    $basket = $order->getBasket();
    $propsCollection = $order->getPropertyCollection();

    // Основные контакты (набор 1)
    $fio = (string) getPropertyByCodeClear($propsCollection, 'FIO');
    $lname = (string) getPropertyByCodeClear($propsCollection, 'LAST_NAME');
    $adresa = (string) getPropertyByCodeClear($propsCollection, 'Adresa');
    $mesto = (string) getPropertyByCodeClear($propsCollection, 'Mesto');
    $psc = (string) getPropertyByCodeClear($propsCollection, 'PSС');
    $email = (string) getPropertyByCodeClear($propsCollection, 'EMAIL');
    $phone = (string) getPropertyByCodeClear($propsCollection, 'PHONE');
    $main_firma = (string) getPropertyByCodeClear($propsCollection, 'Firma');
    $main_ico = (string) getPropertyByCodeClear($propsCollection, 'IČO');
    $main_icdph = (string) getPropertyByCodeClear($propsCollection, 'IČ DPH');
    $main_dic = (string) getPropertyByCodeClear($propsCollection, 'DIČ');

    // Опциональный набор 2 — доставка на другую адресу
    $fio2 = (string) getPropertyByCodeClear($propsCollection, 'FIO_2');
    $lname2 = (string) getPropertyByCodeClear($propsCollection, 'LAST_NAME_2');
    $adresa2 = (string) getPropertyByCodeClear($propsCollection, 'Adresa_2');
    $mesto2 = (string) getPropertyByCodeClear($propsCollection, 'Mesto_2');
    $psc2 = (string) getPropertyByCodeClear($propsCollection, 'PSС_2');
    $phone2 = (string) getPropertyByCodeClear($propsCollection, 'PHONE_2');

    $hasDeliveryAddress = !empty($fio2) || !empty($lname2) || !empty($adresa2) || !empty($mesto2) || !empty($psc2) || !empty($phone2);

    $shipment_name = '';
    $shipmentCollection = $order->getShipmentCollection();
    foreach ($shipmentCollection as $shipment) {
        $pieces = explode('(', $shipment->getDeliveryName());
        $shipment_name = trim($pieces[0]);
        break;
    }

    $payment_name = '';
    foreach ($order->getPaymentCollection() as $payment) {
        $payment_name = $payment->getPaymentSystemName();
        break;
    }

    $zas_add = '';
    $zas_place = '';
    if (getPropertyByCode($propsCollection, 'zas_add')) {
        $zas_add = getPropertyByCode($propsCollection, 'zas_add')->getValue();
    }
    if (getPropertyByCode($propsCollection, 'zas_place')) {
        $zas_place = getPropertyByCode($propsCollection, 'zas_place')->getValue();
    }

    // Адрес одной строкой (mesto, adresa, psc)
    $addressLine = trim(implode(', ', array_filter([$mesto, $adresa, $psc])), " \t\n\r\0\x0B,");
    $addressLine2 = trim(implode(', ', array_filter([$mesto2, $adresa2, $psc2])), " \t\n\r\0\x0B,");

    $orderDate = $order->getDateInsert();
    if ($orderDate instanceof \Bitrix\Main\Type\DateTime) {
        $orderDateFormatted = $orderDate->format('j. F Y, H:i');
    } else {
        $orderDateFormatted = date('j. F Y, H:i', strtotime($orderDate));
    }

    $basketTotal = 0;
    $productRows = '';
    $productIndex = 0;

    foreach ($basket as $basketItem) {
        $arMeasure = Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($basketItem->getProductId());
        $basketPropertyCollection = $basketItem->getPropertyCollection();
        $props = $basketPropertyCollection->getPropertyValues();

        $name = $basketItem->getField('NAME');

        $productId = $basketItem->getProductId();

        $mxResult = \CCatalogSku::GetProductInfo($productId);

        if (is_array($mxResult)){
            $productId = $mxResult['ID'];
        }

        $res = CIBlockElement::GetByID($productId);

        $productPicture = [];

        if ($product = $res->GetNext()) {
            $fileID = !empty($product['PREVIEW_PICTURE']) ? $product['PREVIEW_PICTURE'] : $product['DETAIL_PICTURE'];
            $productPicture = CFile::ResizeImageGet($fileID, array('width' => 100, 'height' => 100));
        }

        $extras = [];
        if (!empty($props['SIZES_STR']['VALUE'])) {
            $sizeLines = !empty($props['SIZES_STR']['VALUE'])
                ? Format::sizeLines($props['SIZES_STR']['VALUE'])
                : [];

            $extras[] = 'Rozmery: </br>';

            foreach ($sizeLines as $sizeLine):
              $extras[] = $sizeLine;
            endforeach;
        }
        if (!empty($props['SQUARE']['VALUE'])) {
            $extras[] = 'Plocha: ' . $props['SQUARE']['VALUE'] . ' m2';
        }
        if (!empty($props['COLOR']['VALUE'])) {
            $extras[] = 'Farba: ' . $props['COLOR']['VALUE'];
        }
        $detailText = implode('<br>', $extras);
        if (!empty($props['SIZES_STR']['VALUE'])) {
            $qtyText = '1 ks';
            $linePrice = $basketItem->getPrice();
        } else {
            $symbol = isset($arMeasure[$basketItem->getProductId()]['MEASURE']['SYMBOL_RUS'])
                ? $arMeasure[$basketItem->getProductId()]['MEASURE']['SYMBOL_RUS']
                : 'ks';
            $qtyText = $basketItem->getQuantity() . ' ' . $symbol;
            $linePrice = $basketItem->getQuantity() * $basketItem->getPrice();
        }
        $basketTotal += $linePrice;

        $productRows .= '
                                <tr>
                                    <td style="padding: 20px 15px; border-bottom: 1px solid #e9ecef;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-right: 15px; vertical-align: top;">
                                                    <img src="' . (!empty($productPicture["src"]) ? $productPicture["src"] : '') . '" alt="Tovar" width="80" height="80" style="display: block; border-radius: 6px; border: 1px solid #e9ecef;">
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <p style="margin: 0 0 5px 0; color: #212529; font-size: 15px; font-weight: 500;">' . htmlspecialchars($name) . '</p>
                                                    <p style="margin: 0; color: #6c757d; font-size: 13px;">' . ($detailText ? $detailText . '<br>' : '') . 'Množstvo: ' . htmlspecialchars($qtyText) . '</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="padding: 20px 15px; border-bottom: 1px solid #e9ecef; text-align: right; vertical-align: top;">
                                        <p style="margin: 0; color: #212529; font-size: 16px; font-weight: 600;">' . number_format($linePrice, 2, ',', ' ') . ' €</p>
                                    </td>
                                </tr>';
    }

    $deliveryPrice = $order->getDeliveryPrice();
    $orderTotal = $order->getPrice();
    $basketTotalFormatted = number_format($basketTotal, 2, ',', ' ');
    $deliveryFormatted = number_format($deliveryPrice, 2, ',', ' ');
    $totalFormatted = number_format($orderTotal, 2, ',', ' ');

    // Блок контактов — основной
    $customerRows = '';
    if ($fio !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px; width: 140px;">Meno:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($fio) . '</td></tr>';
    }
    if ($lname !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Priezvisko:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($lname) . '</td></tr>';
    }
    if ($phone !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Telefón:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($phone) . '</td></tr>';
    }
    if ($email !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Email:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($email) . '</td></tr>';
    }
    if ($addressLine !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px; vertical-align: top;">Adresa:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($addressLine) . '</td></tr>';
    }
    if ($main_firma !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Spoločnosť:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($main_firma) . '</td></tr>';
    }
    if ($main_ico !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">IČO:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($main_ico) . '</td></tr>';
    }
    if ($main_icdph !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">IČ DPH:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($main_icdph) . '</td></tr>';
    }
    if ($main_dic !== '') {
        $customerRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">DIČ:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($main_dic) . '</td></tr>';
    }
    if ($customerRows === '') {
        $customerRows = '<tr><td style="padding: 8px 0; color: #6c757d;" colspan="2">—</td></tr>';
    }

    // Опциональный блок «Doručenie na inú adresu» (набор контактов 2)
    $deliveryAddressRows = '';
    if ($hasDeliveryAddress) {
        if ($fio2 !== '') {
            $deliveryAddressRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px; width: 140px;">Meno:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($fio2) . '</td></tr>';
        }
        if ($lname2 !== '') {
            $deliveryAddressRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Priezvisko:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($lname2) . '</td></tr>';
        }
        if ($phone2 !== '') {
            $deliveryAddressRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Telefón:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($phone2) . '</td></tr>';
        }
        if ($addressLine2 !== '') {
            $deliveryAddressRows .= '<tr><td style="padding: 8px 0; color: #6c757d; font-size: 14px; vertical-align: top;">Adresa:</td><td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 500;">' . htmlspecialchars($addressLine2) . '</td></tr>';
        }
    }

    $deliveryDetailsHtml = $shipment_name;
    if ($zas_add !== '') {
        $deliveryDetailsHtml .= '<br><span style="color: #6c757d; font-size: 13px; font-weight: 400;">Zásielkovňa: ' . htmlspecialchars($zas_add) . ($zas_place !== '' ? ', ' . htmlspecialchars($zas_place) : '') . '</span>';
    }

    // Header (комментарий: <!-- Header -->)
    $arFields['EMAIL_HEADER'] = '
                    <tr>
                        <td style="background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">
                                Nová objednávka
                            </h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px; font-weight: 400;">
                                Ďakujeme za vašu objednávku!
                            </p>
                        </td>
                    </tr>';

    // Order Number (комментарий: <!-- Order Number -->)
    $arFields['EMAIL_ORDER_NUMBER'] = '
                    <tr>
                        <td style="padding: 30px 30px 20px 30px; background-color: #ffffff;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="background-color: #f8f9fa; padding: 15px 20px; border-radius: 6px; border-left: 4px solid #4A90E2;">
                                        <p style="margin: 0; color: #495057; font-size: 14px; font-weight: 500;">
                                            Číslo objednávky: <span style="color: #4A90E2; font-weight: 600;">#' . (int)$orderID . '</span>
                                        </p>
                                        <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 13px;">
                                            Dátum: ' . htmlspecialchars($orderDateFormatted) . '
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

    // Customer Info (комментарий: <!-- Customer Info -->)
    $arFields['EMAIL_CUSTOMER_INFO'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px; font-weight: 600;">
                                Údaje zákazníka
                            </h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            ' . $customerRows . '
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

    // Delivery to other address — опционально (набор контактов 2)
    $arFields['EMAIL_DELIVERY_ADDRESS'] = '';
    if ($hasDeliveryAddress && $deliveryAddressRows !== '') {
        $arFields['EMAIL_DELIVERY_ADDRESS'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px; font-weight: 600;">
                                Doručenie na inú adresu
                            </h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            ' . $deliveryAddressRows . '
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
    }

    // Products Table (комментарий: <!-- Products Table -->)
    $arFields['EMAIL_PRODUCTS_TABLE'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 20px; font-weight: 600;">
                                Zloženie objednávky
                            </h2>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse;">
                                <tr style="background-color: #f8f9fa;">
                                    <td style="padding: 15px; border-bottom: 2px solid #e9ecef; color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Tovar
                                    </td>
                                    <td style="padding: 15px; border-bottom: 2px solid #e9ecef; color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: right; width: 120px;">
                                        Cena
                                    </td>
                                </tr>
                                ' . $productRows . '
                            </table>
                        </td>
                    </tr>';

    // Order Summary (комментарий: <!-- Order Summary -->)
    $arFields['EMAIL_ORDER_SUMMARY'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="right" style="padding: 20px; background-color: #f8f9fa; border-radius: 6px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="right">
                                            <tr>
                                                <td style="padding: 5px 0; color: #6c757d; font-size: 14px; text-align: right; padding-right: 20px;">
                                                    Tovary:
                                                </td>
                                                <td style="padding: 5px 0; color: #212529; font-size: 14px; font-weight: 500; text-align: right; min-width: 100px;">
                                                    ' . $basketTotalFormatted . ' €
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 5px 0; color: #6c757d; font-size: 14px; text-align: right; padding-right: 20px;">
                                                    Doprava:
                                                </td>
                                                <td style="padding: 5px 0; color: #212529; font-size: 14px; font-weight: 500; text-align: right;">
                                                    ' . $deliveryFormatted . ' €
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 15px 0 5px 0; border-top: 2px solid #dee2e6; color: #212529; font-size: 18px; font-weight: 600; text-align: right; padding-right: 20px;">
                                                    Celkom:
                                                </td>
                                                <td style="padding: 15px 0 5px 0; border-top: 2px solid #dee2e6; color: #4A90E2; font-size: 20px; font-weight: 700; text-align: right;">
                                                    ' . $totalFormatted . ' €
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

    // Delivery & Payment (комментарий: <!-- Delivery & Payment -->)
    $arFields['EMAIL_DELIVERY_PAYMENT'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td width="50%" style="padding-right: 15px; vertical-align: top;">
                                        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #4A90E2;">
                                            <p style="margin: 0 0 8px 0; color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Doprava
                                            </p>
                                            <p style="margin: 0; color: #212529; font-size: 15px; font-weight: 500;">
                                                ' . $deliveryDetailsHtml . '
                                            </p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding-left: 15px; vertical-align: top;">
                                        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #357ABD;">
                                            <p style="margin: 0 0 8px 0; color: #6c757d; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Platba
                                            </p>
                                            <p style="margin: 0; color: #212529; font-size: 15px; font-weight: 500;">
                                                ' . htmlspecialchars($payment_name) . '
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

    // Thank You Message (комментарий: <!-- Thank You Message -->)
    $arFields['EMAIL_THANK_YOU_MESSAGE'] = '
                    <tr>
                        <td style="padding: 0 30px 30px 30px; background-color: #ffffff;">
                            <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 30px; border-radius: 6px; text-align: center;">
                                <p style="margin: 0 0 10px 0; color: #212529; font-size: 18px; font-weight: 600;">
                                    Ďakujeme za vašu objednávku!
                                </p>
                                <p style="margin: 0; color: #6c757d; font-size: 14px; line-height: 1.6;">
                                    Dostali sme vašu objednávku a začali sme ju spracovávať.<br>
                                    Dostanete upozornenie o stave objednávky na uvedený email.
                                </p>
                            </div>
                        </td>
                    </tr>';
}

include_once("classes/handler/admin/OrderTabs.php");

function send_sms_message($phone_number, $message) {
    // Ключ лежит в settings.php: тот файл не выгружается на боевой сервер
    // и не попадает в резервные копии кода. Если его нет - молча не шлём,
    // но пишем в журнал: лучше остаться без SMS, чем уронить оформление
    $apiKey = defined('SMS_API_KEY') ? SMS_API_KEY : '';
    $senderName = defined('SMS_SENDER_NAME') ? SMS_SENDER_NAME : 'Luxsol';

    if ($apiKey === '') {
        AddMessage2Log('SMS не отправлена: в settings.php не задан SMS_API_KEY', 'sms_service');

        return false;
    }

    try {
        $smsService = new Services\SmsService($apiKey, $senderName, true, true);
        $result = $smsService->sendMessage($phone_number, $message);

        // Функция не прерывает выполнение при ошибке - возвращает false
        // Ошибки логируются внутри сервиса
        return $result;
    } catch (\Exception $e) {
        // Дополнительная защита - даже если произошло исключение,
        // функция не прерывает выполнение родительского кода
        AddMessage2Log('SMS отправка: критическая ошибка - ' . $e->getMessage());
        return false;
    }
}

//var_dump(send_sms_message(2384723847823, 'test message'));

//function create_invoice($order) {
//
//		$propertyCollection = $order->getPropertyCollection();
//
//		$order_id = $order->getId();
//		$delivery_price = $order->getDeliveryPrice();
//		$basket = $order->getBasket();
//		$total_price = $order->getPrice();
//		$person_type = $order->getPersonTypeId();
//
//		$invoice_data = [];
//
//		$invoice_data['documentNumber'] = $order_id;
//		$invoice_data['numberingSequence'] = '';
//		$invoice_data['totalPrice'] = $total_price;
//		$invoice_data['totalPriceWithVat'] = $total_price;
//		$invoice_data['createDate'] = date('Y-m-d H:i:s');
//
//		$invoice_data['currency'] = 'EUR';
//		$invoice_data['exchangeRate'] = 1;
//		$invoice_data['UseOssTax'] = false;
//
//
//		$isNatural = $person_type == 1;
//
//		$invoice_data['IsNaturalPerson'] = $isNatural;
//
//		/* CLIENT INFO */
//
//		$main_name = getPropertyByCodeClear($propertyCollection, 'FIO');
//		$main_last_name = getPropertyByCodeClear($propertyCollection, 'LAST_NAME');
//
//		$main_client_name = '';
//
//		if(!empty($main_name)) $main_client_name .= $main_name;
//		if(!empty($main_last_name)) $main_client_name .= ' '.$main_last_name;
//
//		$main_address = getPropertyByCodeClear($propertyCollection, 'Adresa');
//		$main_psc = getPropertyByCodeClear($propertyCollection, 'PSС');
//		$main_mesto = getPropertyByCodeClear($propertyCollection, 'Mesto');
//		$main_phone = '+'.getPropertyByCodeClear($propertyCollection, 'PHONE');
//		$main_email = getPropertyByCodeClear($propertyCollection, 'EMAIL');
//
//		if(!$isNatural) {
//			$invoice_data['clientRegistrationId'] = getPropertyByCodeClear($propertyCollection, 'IČO');
//			$invoice_data['clientTaxId'] = getPropertyByCodeClear($propertyCollection, 'DIČ');
//			$invoice_data['clientVatId'] = getPropertyByCodeClear($propertyCollection, 'IČ DPH');
//			$invoice_data['clientName'] = getPropertyByCodeClear($propertyCollection, 'Firma');
//			$invoice_data['clientContactName'] = $main_client_name;
//		} else {
//			$invoice_data['clientName'] = $main_client_name;
//		}
//
//		$invoice_data['clientStreet'] = $main_address;
//		$invoice_data['clientPostCode'] = $main_psc;
//		$invoice_data['clientTown'] = $main_mesto;
//		$invoice_data['clientPhone'] = $main_phone;
//		$invoice_data['clientCountry'] = $main_phone;
//		$invoice_data['clientEmail'] = $main_email;
//
//		/* CLIENT POSTAL INFO */
//
//		$name = getPropertyByCodeClear($propertyCollection, 'FIO_2');
//		$last_name = getPropertyByCodeClear($propertyCollection, 'LAST_NAME_2');
//
//		$client_name = '';
//
//		if(!empty($name)) $client_name .= $name;
//		if(!empty($last_name)) $client_name .= ' '.$last_name;
//
//		$phone = getPropertyByCodeClear($propertyCollection, 'PHONE_2');
//		$mesto = getPropertyByCodeClear($propertyCollection, 'Mesto_2');
//		$psc = getPropertyByCodeClear($propertyCollection, 'PSС_2');
//		$address = getPropertyByCodeClear($propertyCollection, 'Adresa_2');
//
//		$isDifferent = !empty($psc) && !empty($mesto) && !empty($address);
//
//		$invoice_data['clientHasDifferentPostalAddress'] = $isDifferent;
//
//		$invoice_data['clientPostalCountry'] = !empty(trim($phone)) ? '+'. $phone : $main_phone;
//		$invoice_data['clientPostalTown'] = !empty(trim($mesto)) ? $mesto : $main_mesto;
//		$invoice_data['clientPostalPostCode'] = !empty(trim($psc)) ? $psc : $main_psc;
//		$invoice_data['clientPostalStreet'] = !empty(trim($address)) ? $address : $main_address;
//		$invoice_data['clientPostalPhone'] = !empty(trim($phone)) ? '+'. $phone : $main_phone;
//
//		if(!$isNatural) {
//			$invoice_data['clientPostalName'] = getPropertyByCodeClear($propertyCollection, 'Firma');
//			$invoice_data['clientPostalContactName'] = !empty(trim($client_name)) ? $client_name : $main_client_name;
//		} else {
//			$invoice_data['clientPostalName'] = !empty(trim($client_name)) ? $client_name : $main_client_name;
//		}
//
//		/* SENDER INFO */
//
//		// senderName
//
//		/* OTHER INFO */
//
//		$invoice_data['senderIsVatPayer'] = true;
//		$invoice_data['priceDecimalPlaces'] = 2;
//
//		/* PD INFO */
//
//		$order_pays = $order->getPaymentSystemId();
//
//		$payment_name = 'Bankový prevod';
//
//		if(in_array(5, $order_pays)) {
//			$payment_name = 'Bankový prevod';
//		}elseif(in_array(6, $order_pays)) {
//			$payment_name = 'Dobierka';
//		}elseif(in_array(7, $order_pays)) {
//			$payment_name = 'Kreditná karta';
//		}elseif(in_array(4, $order_pays)) {
//			$payment_name = 'Hotovosť';
//		}
//
//		$invoice_data['paymentType'] = $payment_name;
//
//		$deliveryIds = $order->getDeliverySystemId();
//		$delivery_price = 0;
//
//		if(in_array(21, $deliveryIds)) {
//			$delivery_name = 'Poštová zásielka';
//			$delivery_price = 3.5;
//		}elseif(in_array(20, $deliveryIds)){
//			$delivery_name = 'Osobný odber';
//			$delivery_price = 0;
//		}elseif(in_array(18, $deliveryIds)) {
//			$delivery_name = 'Kuriérska služba DPD';
//			$delivery_price = 6;
//		}
//
//		if($payment_name == 'Dobierka') {
//			$delivery_price += 1;
//		}
//
//		$delivery_price = $delivery_price / 1.2;
//
//		$invoice_data['deliveryType'] = $delivery_name;
//
//		/* CART */
//
//		$invoice_data['items'] = [];
//
//		$counter = 0;
//
//		foreach ($basket as $basketItem):
//			$arMeasure = Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($basketItem->getProductId());
//			$basketPropertyCollection = $basketItem->getPropertyCollection();
//			$props = $basketPropertyCollection->getPropertyValues();
//
//			$invoice_data['items'][$counter]['name'] = $basketItem->getField('NAME');
//			$invoice_data['items'][$counter]['count'] = empty($props['SIZES_STR']['VALUE']) ? $basketItem->getQuantity() : count(explode(PHP_EOL, $props['SIZES_STR']['VALUE'])) - 1;
//			$invoice_data['items'][$counter]['measureType'] = $arMeasure[$basketItem->getProductId()]['MEASURE']['SYMBOL_RUS'];
//
//			//$invoice_data['items'][$counter]['totalPrice'] = $basketItem->getFinalPrice();
//			//$invoice_data['items'][$counter]['unitPrice'] = 0;
//			$invoice_data['items'][$counter]['unitPrice'] = !empty($props['SIZES_STR']['VALUE']) ? round($props['PRICE_NOVAT']['VALUE'] / $invoice_data['items'][$counter]['count'], 2) : $props['PRICE_NOVAT']['VALUE'];
//
//			$invoice_data['items'][$counter]['vat'] = 20;
//			$invoice_data['items'][$counter]['description'] = !empty($props['SIZES_STR']['VALUE']) ? $props['SIZES_STR']['VALUE'] : '';
//
//			$counter++;
//		endforeach;
//
//		$length = count($invoice_data['items']);
//
//		$invoice_data['items'][$length]['name'] = $delivery_name;
//		$invoice_data['items'][$length]['count'] = 1;
//		$invoice_data['items'][$length]['unitPrice'] = round($delivery_price, 2);
//		$invoice_data['items'][$length]['totalPrice'] = round($delivery_price, 2);
//		$invoice_data['items'][$length]['vat'] = 20;
//		$invoice_data['items'][$length]['measureType'] = '';
//		$invoice_data['items'][$length]['typeId'] = 2;
//
//		$sendData = [$invoice_data];
//
//		$ch = curl_init();
//
//		curl_setopt($ch, CURLOPT_URL, "https://eshops.inteo.sk/api/v1/incomingorders/");
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//		curl_setopt($ch, CURLOPT_HEADER, FALSE);
//		curl_setopt($ch, CURLOPT_POST, TRUE);
//
//		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendData));
//
// (удалён забытый блок с чужим токеном авторизации)
//
//		$response = curl_exec($ch);
//		curl_close($ch);
//
//		$result = json_decode($response, true);
//}

function checkPhoneNumber($phone) {
    if (empty($phone) || !is_string($phone)) {
        return false;
    }

    // Нормализация номера: удаление пробелов, дефисов, скобок и других символов
    $normalizedPhone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

    // Если номер начинается с +, удаляем его для проверки
    if (strpos($normalizedPhone, '+') === 0) {
        $normalizedPhone = substr($normalizedPhone, 1);
    }

    // Массив паттернов для различных стран
    // Формат: код страны без +, затем паттерн для остальной части номера
    $patterns = [
        '/^43[0-9]{8,13}$/',                    // Austria
        '/^9710?[2-7,9]\d{8}$/',               // UAE
        '/^32[0-9]{9,10}$/',                   // Belgium
        // Словакия: только мобильные. Они всегда начинаются на 9, а 900 -
        // платный сервисный диапазон, не абонентский. Стационарные номера
        // (02, 031-058) SMS не принимают, отправка на них - деньги впустую
        '/^421(?!900)9[0-9]{8}$/',            // Slovakia
        '/^359(2\d{7}|8\d{8}|[13-79]\d{7,8})$/', // Bulgaria (исправлена ошибка с &#124;)
        '/^385[0-9]{8,9}$/',                  // Croatia
        '/^420[0-9]{9}$/',                    // Czech Republic
        '/^45[0-9]{8}$/',                     // Denmark
        '/^372[0-9]{7,8}$/',                 // Estonia
        '/^358[0-9]{7,10}$/',                // Finland
        '/^33[0-9]{9}$/',                     // France
        '/^49[0-9]{7,12}$/',                  // Germany
        '/^30[0-9]{10}$/',                    // Greece
        '/^36[0-9]{8,9}$/',                  // Hungary
        '/^353[0-9]{8,9}$/',                 // Ireland
        '/^39[0-9]{8,12}$/',                 // Italy
        '/^3712[0-9]{7}$/',                  // Latvia
        '/^423[0-9]{7,9}$/',                 // Liechtenstein
        '/^370[0-9]{8,9}$/',                 // Lithuania
        '/^352[0-9]{4,11}$/',                // Luxembourg
        '/^356[0-9]{8}$/',                    // Malta
        '/^382[0-9]{6,8}$/',                 // Montenegro
        '/^31[0-9]{9}$/',                     // Netherlands
        '/^48[0-9]{9}$/',                     // Poland
        '/^351[0-9]{9}$/',                    // Portugal
        '/^400?[1-9]\d{8}$/',                 // Romania
        '/^386[0-9]{8}$/',                    // Slovenia
        '/^34[0-9]{9}$/',                     // Spain
        '/^46[0-9]{7,12}$/',                  // Sweden
        '/^41[0-9]{9}$/',                     // Switzerland
        '/^380[0-9]{7,9}$/',                  // Ukraine
        '/^44[0-9]{4,10}$/',                 // UK
    ];

    // Заведомо выдуманные номера: одинаковые цифры или нулевой абонентский
    // номер. Такие приходят из тестов и опечаток, SMS по ним улетает впустую
    $subscriber = preg_replace('/^(42[01]|4[0-9]{1,2})/', '', $normalizedPhone);

    if ($subscriber !== '' && preg_match('/^(\d)\1*$/', $subscriber)) {
        return false;
    }

    // Проверка каждого паттерна
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $normalizedPhone)) {
            return true;
        }
    }

    return false;
}

function createPacketa($order) {
    // Packeta - использует новый сервис PacketaService
    // API пароль для Packeta (можно вынести в конфигурацию)
    // Пароль лежит в settings.php: файл не выгружается на сервер
$apiPassword = defined('PACKETA_API_PASSWORD') ? PACKETA_API_PASSWORD : '';

    try {
        $packetaService = new Services\PacketaService($apiPassword);
        $packetaService->createPacket($order);
    } catch (\Exception $e) {
        // Логирование ошибки
        error_log('Packeta createPacket error: ' . $e->getMessage());
    }
}

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'onSaleOrderSavedHandler'
);

function onSaleOrderSavedHandler(\Bitrix\Main\Event $event)
{
    if(!$event->getParameter("IS_NEW")) return;

    $order = $event->getParameter("ENTITY");
    $order_id = $order->getId();

    $propertyCollection = $order->getPropertyCollection();
    $phonePropValue = $propertyCollection->getPhone();

    /* SEND FACTURA AND SMS */

    // $order_pays = $order->getPaymentSystemId();

    $order_delivery = $order->getDeliverySystemId();

    if (in_array(21, $order_delivery)) {
        createPacketa($order);
    }

    $phone = $phonePropValue->getValue();
    $is_valid_phone = checkPhoneNumber($phone);

    if ($is_valid_phone) {
        send_sms_message($phone, 'Vasa obj. '.$order_id.' bola prijata. Dalej Vas budeme informovat SMS. Luxsol.sk (na SMS neodpovedajte)');
    }
}

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleStatusOrderChange',
    'OnSaleStatusOrderChangeHandler'
);

function OnSaleStatusOrderChangeHandler($event)
{
    $parameters = $event->getParameters();

    if ($parameters['VALUE'] === 'OT')
    {
        $order = $parameters['ENTITY'];
        $order_id = $order->getId();

        $propertyCollection = $order->getPropertyCollection();
        $phonePropValue = $propertyCollection->getPhone();

        $phone = $phonePropValue->getValue();
        $is_valid_phone = checkPhoneNumber($phone);

        if ($is_valid_phone) {
            send_sms_message($phone, 'Vazeny klient, obj. '.$order_id.' bola prave vybavena a caka na vyzdvihnutie kuriérom. Dakujeme ! www.luxsol.sk');
        }
    }

    if ($parameters['VALUE'] === 'LP')
    {
        $order = $parameters['ENTITY'];
        $order_id = $order->getId();

        $propertyCollection = $order->getPropertyCollection();
        $phonePropValue = $propertyCollection->getPhone();

        $phone = $phonePropValue->getValue();
        $is_valid_phone = checkPhoneNumber($phone);

        if ($is_valid_phone) {
            send_sms_message($phone, 'Objednávka číslo '.$order_id.' je pripravená k odberu na adrese Bratislava, Račianska 66. Termín vyzdvihnutia prosím nahlásiť telefonicky alebo mailom. Ďakujem, Luxsol.sk');
        }
    }

    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS
    );
}

\Bitrix\Main\EventManager::getInstance()->addEventHandlerCompatible(
    'sale',
    'OnSaleComponentOrderProperties',
    'SaleOrderEvents::fillLocation'
);

class SaleOrderEvents
{
    public static function fillLocation(&$arUserResult, $request, &$arParams, &$arResult)
    {
        $registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
        $orderClassName = $registry->getOrderClassName();
        $order = $orderClassName::create(\Bitrix\Main\Application::getInstance()->getContext()->getSite());
        $propertyCollection = $order->getPropertyCollection();

        foreach ($propertyCollection as $property)
        {
            if ($property->isUtil())
                continue;

            $arProperty = $property->getProperty();

            if(
                $arProperty['TYPE'] === 'LOCATION'
                && array_key_exists($arProperty['ID'],$arUserResult["ORDER_PROP"])
                && !$request->getPost("ORDER_PROP_".$arProperty['ID'])
                && (
                    !is_array($arOrder=$request->getPost("order"))
                    || !$arOrder["ORDER_PROP_".$arProperty['ID']]
                )
            ) {
                $arUserResult["ORDER_PROP"][$arProperty['ID']] = '0000027973';
            }
        }
    }
}

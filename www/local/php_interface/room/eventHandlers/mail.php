<?

// AddEventHandler('main', 'OnBeforeEventAdd', ['\Room\Events\MailHandlers', 'OnBeforeEventAddHandler']);
AddEventHandler("sale", "OnOrderNewSendEmail", ['\Room\Events\MailHandlers', 'OnBeforeEventAddHandler']);
AddEventHandler('main', 'OnBeforeEventSend', ['\Room\Events\MailHandlers', 'preparePaymentNotification']);
AddEventHandler('main', 'OnBeforeEventSend', ['\Room\Events\MailHandlers', 'prepareNewOrderNotification']);
\Bitrix\Main\EventManager::getInstance()->addEventHandler('main', 'OnBeforeMailSend', ['\Room\Events\MailHandlers', 'prepareCustomerMail']);

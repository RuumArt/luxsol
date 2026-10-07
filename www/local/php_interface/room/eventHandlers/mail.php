<?

// AddEventHandler('main', 'OnBeforeEventAdd', ['\Room\Events\MailHandlers', 'OnBeforeEventAddHandler']);
AddEventHandler("sale", "OnOrderNewSendEmail", ['\Room\Events\MailHandlers', 'OnBeforeEventAddHandler']);
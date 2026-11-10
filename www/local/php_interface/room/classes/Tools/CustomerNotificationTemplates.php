<?php
namespace Room\Tools;

/** Editable Bitrix templates sharing the order-confirmation colours and typography. */
class CustomerNotificationTemplates
{
    public const THEME = 'luxsol_mail';
    public const PRODUCTION_EVENT = 'LUXSOL_PRODUCTION_UPDATE';

    public static function definitions(): array
    {
        return [
            'SALE_STATUS_CHANGED_RE' => ['file'=>'ready-to-ship', 'title'=>'Objednávka je pripravená na odoslanie',
                'subject'=>'Objednávka č. #ORDER_ID# je pripravená na odoslanie'],
            'SALE_STATUS_CHANGED_LP' => ['file'=>'ready-for-pickup', 'title'=>'Objednávka je pripravená na osobný odber',
                'subject'=>'Objednávka č. #ORDER_ID# je pripravená na osobný odber'],
            self::PRODUCTION_EVENT => ['file'=>'production-update', 'title'=>'Informácia o stave objednávky',
                'subject'=>'Informácia o stave objednávky č. #ORDER_NUMBER#'],
            AdvanceInvoiceReminderTemplate::EVENT_NAME => ['file'=>'advance-invoice-reminder', 'title'=>'Pripomenutie platby',
                'subject'=>'Pripomenutie platby k objednávke č. #ORDER_NUMBER# – Luxsol.sk'],
        ];
    }

    public static function fields(string $event, array $source, string $site = 's1'): array
    {
        $definition = self::definitions()[$event] ?? null;
        if (!$definition) { throw new \InvalidArgumentException('Unknown customer notification.'); }
        $body = file_get_contents(__DIR__ . '/../../../../email-templates/' . $definition['file'] . '.sk.html');
        if ($body === false) { throw new \RuntimeException('Missing notification body.'); }
        return [
            'ACTIVE'=>'Y', 'EVENT_NAME'=>$event, 'LID'=>[$site], 'SITE_TEMPLATE_ID'=>self::THEME,
            'LANGUAGE_ID'=>$source['LANGUAGE_ID'] ?? null, 'EMAIL_FROM'=>'info@luxsol.sk',
            'EMAIL_TO'=>'#EMAIL#', 'REPLY_TO'=>'office@luxsol.sk', 'BCC'=>'', 'CC'=>'',
            'SUBJECT'=>$definition['subject'], 'BODY_TYPE'=>'html', 'MESSAGE'=>$body,
        ];
    }
}

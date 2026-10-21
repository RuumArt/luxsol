<?php

namespace Room\Tools;

/** Definition of an editable Bitrix mail template; does not queue or send mail. */
class AdvanceInvoiceReminderTemplate
{
    public const EVENT_NAME = 'LUXSOL_ADVANCE_INVOICE_REMINDER';

    public const DESCRIPTION = <<<'TXT'
#EMAIL# - E-mail zákazníka
#ORDER_NUMBER# - Číslo objednávky
V HTML tele sa používajú bezpečne escapované varianty #HTML_NAZOV_POLA#.
Automaticky na 3. a 5. deň od prechodu do Odoslaná ZF, iba nevyplatený bankový prevod.
Číslo faktúry a platobné údaje sa neuvádzajú; zákazník použije už zaslanú faktúru.
TXT;

    public static function fields(array $source, string $site): array
    {
        if (empty($source['SITE_TEMPLATE_ID']) || ($source['BODY_TYPE'] ?? '') !== 'html') {
            throw new \RuntimeException('Zdrojový zákaznícky e-mail musí používať HTML poštovú tému.');
        }

        $body = file_get_contents(__DIR__ . '/../../../../email-templates/advance-invoice-reminder.sk.html');
        if ($body === false) {
            throw new \RuntimeException('Chýba telo šablóny pripomenutia platby.');
        }

        return [
            'ACTIVE' => 'Y',
            'EVENT_NAME' => self::EVENT_NAME,
            'LID' => [$site],
            // Same mail theme and language selection as the existing new-order mail.
            'SITE_TEMPLATE_ID' => $source['SITE_TEMPLATE_ID'],
            'LANGUAGE_ID' => $source['LANGUAGE_ID'] ?? null,
            'EMAIL_FROM' => $source['EMAIL_FROM'],
            'EMAIL_TO' => '#EMAIL#',
            'REPLY_TO' => 'office@luxsol.sk',
            'BCC' => '',
            'CC' => '',
            'SUBJECT' => 'Pripomenutie platby k objednávke č. #ORDER_NUMBER# – Luxsol.sk',
            'BODY_TYPE' => 'html',
            'MESSAGE' => $body,
        ];
    }
}

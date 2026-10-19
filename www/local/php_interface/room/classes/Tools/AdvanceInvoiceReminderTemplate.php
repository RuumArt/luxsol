<?php

namespace Room\Tools;

/** Definition of an editable Bitrix mail template; does not queue or send mail. */
class AdvanceInvoiceReminderTemplate
{
    public const EVENT_NAME = 'LUXSOL_ADVANCE_INVOICE_REMINDER';

    public const DESCRIPTION = <<<'TXT'
#EMAIL# - E-mail zákazníka
#ORDER_NUMBER# - Číslo objednávky
#INVOICE_NUMBER# - Číslo zálohovej faktúry
#AMOUNT_WITH_CURRENCY# - Suma na úhradu vrátane meny, napr. 23,80 €
#DUE_DATE# - Dátum splatnosti, napr. 23. 09. 2026
#IBAN# - IBAN účtu na úhradu
#VARIABLE_SYMBOL# - Variabilný symbol
V HTML tele sa používajú bezpečne escapované varianty #HTML_NAZOV_POLA#.
Šablóna sama nekontroluje stav platby ani nespúšťa odosielanie.
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
            'SUBJECT' => 'Pripomenutie úhrady zálohovej faktúry č. #INVOICE_NUMBER# – Luxsol.sk',
            'BODY_TYPE' => 'html',
            'MESSAGE' => $body,
        ];
    }
}

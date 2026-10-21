<?php
namespace Room\Tools;

class AdvanceInvoiceReminderLogTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName() { return 'b_room_invoice_reminder'; }

    public static function getMap()
    {
        return [
            'ID' => ['data_type'=>'string', 'primary'=>true, 'validation'=>static fn()=>[new \Bitrix\Main\Entity\Validator\Length(64,64)]],
            'ORDER_ID' => ['data_type'=>'integer', 'required'=>true],
            'STATUS_TS' => ['data_type'=>'integer', 'required'=>true],
            'REMINDER_DAY' => ['data_type'=>'integer', 'required'=>true],
            'RESULT' => ['data_type'=>'string', 'required'=>true],
            'CREATED_AT' => ['data_type'=>'datetime', 'required'=>true],
        ];
    }
}

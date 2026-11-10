<?php
namespace Room\Tools;

/** One persistent reservation per order, including failed or interrupted dispatches. */
class ProductionUpdateLogTable extends \Bitrix\Main\Entity\DataManager
{
    public static function getTableName() { return 'b_room_production_update'; }
    public static function getMap()
    {
        return [
            'ORDER_ID'=>['data_type'=>'integer', 'primary'=>true],
            'RESULT'=>['data_type'=>'string', 'required'=>true],
            'CREATED_AT'=>['data_type'=>'datetime', 'required'=>true],
        ];
    }
}

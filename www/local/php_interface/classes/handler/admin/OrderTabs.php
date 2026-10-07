<?

\Bitrix\Main\EventManager::getInstance()->addEventHandler("main", "OnAdminSaleOrderViewDraggable", array("MyClass1", "onInit"));

class MyClass1
{
    public static function onInit()
        {
            return array("BLOCKSET" => "MyClass1",
                "getScripts"  => array("MyClass1", "mygetScripts"),
                "getBlocksBrief" => array("MyClass1", "mygetBlocksBrief"),
                "getBlockContent" => array("MyClass1", "mygetBlockContent"),
                );
        }
        
    public static function mygetBlocksBrief($args)
        {
            $id = !empty($args['ORDER']) ? $args['ORDER']->getId() : 0;
            return array(
                // 'custom1' => array("TITLE" => "Пользовательский блок для заказа №".$id),
                // 'custom2' => array("TITLE" => "Еще один блок для заказа №".$id),
                );
        }
    
    public static function mygetScripts($args)
        {
            return "<style>
            #sale-adm-user-description-view{
                color: red !important;
                font-weight: bold !important;
                font-size: 16px !important;
                line-height: 22px !important;
            }
            #sale-adm-user-description-view:before{
                content: '‼️ ';
            }
            .adm-s-order-table-ddi-table [data-basket-code] td > span{
                color: #000 !important;
                font-size: 14px !important;
            }
            .adm-s-order-table-ddi-table [data-basket-code] td > div{
                color: #000 !important;
                font-size: 14px !important;
            }

            .bdb-line tr:nth-child(6),
            .bdb-line tr:nth-child(5),
            .bdb-line tr:nth-child(4),
            .bdb-line tr:nth-child(3){
                display: none !important;
            }

            [data-basket-code] td:nth-child(5) tr:nth-child(7) {
                font-weight: bold;
            }

            
            [data-basket-code] td:nth-child(5) tr:nth-child(5),
            [data-basket-code] td:nth-child(5) tr:nth-child(3),
            [data-basket-code] td:nth-child(5) tr:nth-child(4){
                display: none !important;
            }
        </style>";
        }
        
    public static function mygetBlockContent($blockCode, $selectedTab, $args)
        {
        $result = '';
        $id = !empty($args['ORDER']) ? $args['ORDER']->getId() : 0;
        
        if ($selectedTab == 'tab_order')
            {
            if ($blockCode == 'custom1')
                $result = 'Содержимое блока custom1<br> Номер заказа: '.$id;
            if ($blockCode == 'custom2')
                $result = 'Содержимое блока custom2<br> Номер заказа: '.$id;
            }
            
        return $result;
        }
}

?>
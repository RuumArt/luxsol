<?php

AddEventHandler('sale', 'OnSaleStatusOrderChange', ['\Room\Events\SaleHandlers', 'OnSaleStatusOrderChange']);

// Приведение почтового индекса к пяти цифрам и пометка неизвестных индексов
AddEventHandler('sale', 'OnSaleOrderBeforeSaved', ['\Room\Events\SaleHandlers', 'OnSaleOrderBeforeSaved']);

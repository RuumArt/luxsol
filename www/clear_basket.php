<?

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$obBasket = \Bitrix\Sale\Basket::getList(
  array(
      'select'  => array(
          'FUSER_ID'
      ),
      'filter' => array(
          'ORDER_ID' => 'NULL',
          '<DATE_INSERT' => date('d.m.Y', time() - 86400)
      ),
  )
);

while($bItem = $obBasket->Fetch()){
  CSaleBasket::DeleteAll(
      $bItem['FUSER_ID'],
      False
  );
}
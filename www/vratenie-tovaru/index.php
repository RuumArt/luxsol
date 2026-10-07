<?
// Раздел переехал: отказ от договора теперь оформляется онлайн-формой
// на /odstupenie-od-zmluvy/. Постоянный редирект сохраняет старые ссылки
// и позиции страницы в поиске
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
LocalRedirect("/odstupenie-od-zmluvy/", false, "301 Moved permanently");

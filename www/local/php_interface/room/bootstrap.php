<?php

// autoloader
require_once __DIR__ . '/autoload.php';

// constants
require_once __DIR__ . '/settings.php';

\Bitrix\Main\Localization\Loc::loadMessages(__DIR__ . '/labels.php');

// composer
if (file_exists(__DIR__ . '/include/autoload.php')) {
    require_once __DIR__ . '/include/autoload.php';
}

// functions or other files
foreach (glob(__DIR__ . '/functions/*.php') as $file) {
    require_once $file;
}

// events
foreach (glob(__DIR__ . '/eventHandlers/*.php') as $file) {
    require_once $file;
}

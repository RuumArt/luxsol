<?php

namespace Room;

use Room\Layout;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Application
{

    protected static $instance = null;

    public function __construct($siteId = false)
    {

    }

    /**
     * Get instance of class
     * @return type
     */
    public static function getInstance()
    {
        if (!isset(static::$instance))
            static::$instance = new static();

        return static::$instance;
    }

    public function prolog() {
        $layout = Layout\Base::getInstance();

        $layout->addPageClass('page');

        if ($layout->isDarkPage()) {
            $layout->addHeaderClass('header--light');
            $layout->addPageClass('global-page--dark');
        }

        if ($layout->isHomepage()) {
            $layout->setPageName("home");
        }
    }

    public function epilog()
    {
        $layout = Layout\Base::getInstance();

        $layout->setPageClasses();
        $layout->setHeaderClasses();
    }
}

?>
<?php

namespace Room\Layout;

use Bitrix\Main\Application as BitrixApplication;

class Base
{
    protected static ?object $instance = null;
    protected array $pageClasses = [];

    protected string $pageName = 'sample';
    protected array $headerClasses = [];
    protected bool $bHasAjaxContent = false;

    public function __construct()
    {
        if(preg_match( '/macintosh|mac os x/i',  $_SERVER['HTTP_USER_AGENT']))
        {
            $this->bodyClasses[] = "macos";
        }
    }

    /**
     * Get instance of class
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(static::$instance))
            static::$instance = new static();

        return static::$instance;
    }

    public function hasAjaxContent($set = false)
    {
        if ($set)
            $this->bHasAjaxContent = true;

        return $this->bHasAjaxContent;
    }

    public function isHomepage()
    {
        $currentDir = str_replace('//', '/', BitrixApplication::getInstance()->getContext()->getRequest()->getRequestedPageDirectory());
        return $currentDir == SITE_DIR;
    }

    public function isContentPage()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("isContentPage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("isContentPage") == "Y" && !defined('ERROR_404'))
            return true;

        return false;
    }

    public function isSidebarPage()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("isSidebarPage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("isSidebarPage") == "Y")
            return true;

        return false;
    }

//    public function showBreadcrubms()
//    {
//        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bShowBreadcrumbs")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bShowBreadcrumbs") == "Y")
//            return true;
//
//        return false;
//    }

    public function isSimplePage()
    {
        if (defined('ERROR_404') || (strlen($GLOBALS['APPLICATION']->GetDirProperty("isSimplePage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("isSimplePage") == "Y"))
            return true;

        return false;
    }

    public function withoutHeader()
    {
        if (defined('ERROR_404') || (strlen($GLOBALS['APPLICATION']->GetDirProperty("withoutHeader")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("withoutHeader") == "Y"))
            return true;

        return false;
    }

    public function withoutFooter()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bWithoutFooter")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bWithoutFooter") == "Y")
            return true;

        return false;
    }

    public function withoutText()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bWithoutText")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bWithoutText") == "Y")
            return true;

        return false;
    }

    public function isCatalogPage()
    {
        if (defined('ERROR_404')) {
            return false;
        }

        if (defined('CATALOG_PAGE') && CATALOG_PAGE == "Y")
            return true;

        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bCatalogPage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bCatalogPage") == "Y")
            return true;

        return false;
    }

    public function isWithoutTextPage()
    {
        if (defined('WITHOUT_TEXT_PAGE') && WITHOUT_TEXT_PAGE == "Y")
            return true;

        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bWithoutTextPage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bWithoutTextPage") == "Y")
            return true;

        return false;
    }

    public function isHideHeaderAndFooter()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bHideHeaderAndFooter")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bHideHeaderAndFooter") == "Y")
            return true;

        return false;
    }

    public function isShowShortHeaderAndFooter()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bShowShortHeaderAndFooter")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bShowShortHeaderAndFooter") == "Y")
            return true;

        return false;
    }

    public function isHideTitle()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bHideTitle")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bHideTitle") == "Y")
            return true;

        return false;
    }

    public function isHideBreadcrumbs()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bHideBreadcrumbs")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bHideBreadcrumbs") == "Y")
            return true;

        return false;
    }

    public function isDarkPage()
    {
        if (strlen($GLOBALS['APPLICATION']->GetDirProperty("bDarkPage")) > 0 && $GLOBALS['APPLICATION']->GetDirProperty("bDarkPage") == "Y")
            return true;

        return false;
    }

    public function showPageClasses()
    {
        $GLOBALS['APPLICATION']->ShowProperty("room_page_classes");
    }

    public function showHeaderClasses()
    {
        $GLOBALS['APPLICATION']->ShowProperty("room_header_classes");
    }

    public function getHeaderClasses()
    {
        return $GLOBALS['APPLICATION']->GetPageProperty("room_header_classes");
    }

    public function getPageName()
    {
        return $this->pageName;
    }

    public function addPageClass(string $name = "")
    {
        if (!empty($name) && !in_array($name, $this->pageClasses))
        {
            $this->pageClasses[] = (string)$name;
        }
    }

    public function addHeaderClass(string $name = "")
    {
        if (!empty($name) && !in_array($name, $this->headerClasses))
        {
            $this->headerClasses[] = (string)$name;
        }
    }

    public function setPageName(string $name = "")
    {
        $this->pageName = $name;
    }

    public function setPageClasses()
    {
        $GLOBALS['APPLICATION']->SetPageProperty("room_page_classes", implode(" ", $this->pageClasses));
    }

    public function setHeaderClasses()
    {
        $GLOBALS['APPLICATION']->SetPageProperty("room_header_classes", implode(" ", $this->headerClasses));
    }

//    public function setJsVariables()
//    {
//        $variables = array (
//            'LANG_CHARSET' => LANG_CHARSET,
//            'PHONE_NUMBER_MASK' => Options\Base::getInstance(SITE_ID)->getValue('phoneNumberMask')
//        );
//
//        Asset::getInstance()->addString("<script>BX.message(" . \CUtil::PhpToJSObject($variables, false) . ")</script>", true);
//    }
}
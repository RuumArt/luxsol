<?php

namespace Room\Layout;

use Room\Tools;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application as BitrixApplication;

Loc::loadMessages(__FILE__);

class Partial
{

    protected static $instance = null;
    protected $includeDir = "include";
    protected $currentDir = null;
    protected $documentRoot = null;
    protected $bHasAjaxContent = false;

    public function __construct($currentDir)
    {
        $this->currentDir = $currentDir;
        $this->documentRoot = BitrixApplication::getInstance()->getContext()->getServer()->getDocumentRoot();


        if (!empty($this->currentDir))
            $this->includeDir = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->currentDir . DIRECTORY_SEPARATOR . $this->includeDir . DIRECTORY_SEPARATOR);
        else
        {
            $this->includeDir = $this->documentRoot . SITE_DIR . $this->includeDir . DIRECTORY_SEPARATOR;
        }
    }

    public static function getInstance($currentDir = null)
    {
        return new static($currentDir);
    }

    public function render($file, array $data = array(), bool $return = false, bool $copy = true)
    {
        global $APPLICATION;

        $includeFile = null;
        $filePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->includeDir . DIRECTORY_SEPARATOR . $file);
        $copyMode = $copy && $GLOBALS['APPLICATION']->GetShowIncludeAreas();

        if (is_file($filePath) && file_exists($filePath))
        {
            $includeFile = $filePath;
            $filePathParts = pathinfo($filePath);
            $customFilePath = $filePathParts['dirname'] . DIRECTORY_SEPARATOR . $filePathParts['filename'] . "_custom." . $filePathParts['extension'];

            if (file_exists($customFilePath))
            {
                $includeFile = $customFilePath;
            }
        }
        else
        {
            $templateOptionCode = $file;

            if (strpos($templateOptionCode, DIRECTORY_SEPARATOR) !== false)
            {
                $buffer = explode(DIRECTORY_SEPARATOR, $templateOptionCode);
                foreach ($buffer as $key => $word)
                {
                    if ($key > 0)
                        $buffer[$key] = ucfirst($word);
                }

                $templateOptionCode = implode("", $buffer);
            }

            $filePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->includeDir . DIRECTORY_SEPARATOR . $file . ".php");

            if (file_exists($filePath))
            {
                $includeFile = $filePath;
            }
        }

        if (!empty($includeFile))
        {
            if ($return || $copyMode)
                ob_start();

            if (is_array($data))
            {
                $data = array_merge (Array (
                    'APPLICATION' => $GLOBALS['APPLICATION'],
                    'USER' => $GLOBALS['USER'],
                ), $data);

                extract($data);
            }

            require ($includeFile);
        }
        else
        {
            echo Tools\Messages::error('Include area not found (' . $file . ')');
        }
    }
}

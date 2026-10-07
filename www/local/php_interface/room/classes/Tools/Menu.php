<?php

namespace Room\Tools;

class Menu {
    public static function getChildren($input, &$start = 0, $level = 0){
        $children = array();

        if (!$level){
            $lastDepthLevel = 1;
            if(is_array($input)){
                foreach($input as $i => $arItem){
                    if($arItem["DEPTH_LEVEL"] > $lastDepthLevel){
                        if($i > 0){
                            $input[$i - 1]["IS_PARENT"] = 1;
                        }
                    }
                    $lastDepthLevel = $arItem["DEPTH_LEVEL"];
                }
            }
        }

        for ($i = $start, $count = count($input); $i < $count; ++$i){
            $item = $input[$i];
            if($level > $item['DEPTH_LEVEL'] - 1){
                break;
            }
            elseif(!empty($item['IS_PARENT'])){
                ++$i;
                $item['CHILDREN'] = self::getChildren($input, $i, $level + 1);
                --$i;
            }
            $children[] = $item;
        }

        $start = $i;
        return $children;
    }
}
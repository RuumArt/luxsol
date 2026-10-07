<?php

namespace Room\Tools;

class Svg {
    public function getIcon($name)
    {
        return SITE_TEMPLATE_PATH . '/assets/img/sprite.svg#icon-' . $name;
    }
}
<?php

namespace Room\Tools;

class Messages
{
    public static function error($message, $title = "")
    {
        ?>
        <div class="alert alert-danger">
            <?=$message?>
        </div>
        <?
    }
}
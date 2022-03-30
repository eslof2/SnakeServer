<?php

namespace Model;

class Log {
    static function Message(string $msg) {
        echo $msg.PHP_EOL;
    }
}
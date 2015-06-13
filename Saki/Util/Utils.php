<?php

namespace Saki\Util;

class Utils {
    static function str_class_last_part($actualClass, $trimSubString = '') {
        $lastSeparatorPos = strrpos($actualClass, '\\');
        $lastToken = substr($actualClass, $lastSeparatorPos + 1); // DiscardCommand
        $trimmedToken = str_replace($trimSubString, '', $lastToken);
        return $trimmedToken;
    }

    private function __construct() {

    }
}
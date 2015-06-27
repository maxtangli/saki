<?php

namespace Saki\Util;

class Utils {
    static function str_class_last_part($actualClass, $trimSubString = '') {
        $lastSeparatorPos = strrpos($actualClass, '\\');
        $lastToken = substr($actualClass, $lastSeparatorPos + 1); // DiscardCommand
        $trimmedToken = str_replace($trimSubString, '', $lastToken);
        return $trimmedToken;
    }

    /**
     * @param array $arr
     * @param callable $key_selector
     * @return array
     */
    static function array_group_by(array $arr, callable $key_selector) {
        $result = [];
        foreach ($arr as $i) {
            $key = call_user_func($key_selector, $i);
            $result[$key][] = $i;
        }
        return $result;
    }

    static function array_all(array $a, callable $predicate) {
        foreach ($a as $v) {
            if ($predicate($v) == false) {
                return false;
            }
        }
        return true;
    }

    static function array_any(array $a, callable $predicate) {
        foreach ($a as $v) {
            if ($predicate($v) == true) {
                return true;
            }
        }
        return false;
    }

    private function __construct() {

    }
}
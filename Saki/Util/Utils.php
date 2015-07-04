<?php

namespace Saki\Util;

class Utils {
    static function assertEqual($expected, $actual) {
        if ($expected != $actual) {
            throw new \LogicException("Failed; $expected not equals to $actual.");
        }
    }

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

    static function array_max(array $a, callable $selector) {
        return array_reduce($a, function ($maxItem, $currentItem) use ($selector) {
            $greater = $maxItem === null || $selector($currentItem) > $maxItem;
            return $greater ? $currentItem : $maxItem;
        }, null);
    }

    static function array_min(array $a, callable $selector) {
        return array_reduce($a, function ($minItem, $currentItem) use ($selector) {
            $smaller = $minItem === null || $selector($currentItem) < $minItem;
            return $smaller ? $currentItem : $minItem;
        }, null);
    }

    static function array_filter_count(array $a, callable $predicate) {
        return array_reduce($a, function ($carry, $currentItem) use ($predicate) {
            return $predicate($currentItem) ? $carry + 1 : $carry;
        }, 0);
    }

    private function __construct() {

    }
}
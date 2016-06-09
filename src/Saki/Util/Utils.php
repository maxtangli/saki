<?php

namespace Saki\Util;

/**
 * @package Saki\Util
 */
class Utils {
    /**
     * @param int $v
     * @param int $n
     * @return int
     */
    static function normalizedMod(int $v, int $n) {
        if ($n < 0) {
            throw new \InvalidArgumentException();
        }
        return $n == 0 ? 0 : ($v % $n + $n) % $n;
    }

    /**
     * @param $v
     * @param $lowerLimit
     * @param $upperLimit
     * @return bool
     */
    static function inRange($v, $lowerLimit, $upperLimit) {
        return $lowerLimit <= $v && $v <= $upperLimit;
    }

    /**
     * @param string $delimiter
     * @param string $string
     * @return string[]
     */
    static function explodeNotEmpty(string $delimiter, string $string) {
        $tokens = explode($delimiter, $string);
        if ($tokens === false) {
            throw new \InvalidArgumentException();
        }
        return $tokens;
    }

    /**
     * @param $targetValue
     * @param bool $strict
     * @return \Closure
     */
    static function toPredicate($targetValue, bool $strict = false) {
        return function ($v) use ($targetValue, $strict) {
            return $strict ? $v === $targetValue : $v == $targetValue;
        };
    }

    /**
     * @param callable $selector
     * @return \Closure
     */
    static function toComparator(callable $selector) {
        return function ($v1, $v2) use ($selector) {
            return $selector($v1) <=> $selector($v2);
        };
    }

    private function __construct() {
    }
}
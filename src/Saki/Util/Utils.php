<?php

namespace Saki\Util;

/**
 * @package Saki\Util
 */
class Utils {
    private static $tokenNum = 1;

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
     * @param $lower
     * @param $upper
     * @return bool
     */
    static function inRange($v, $lower, $upper) {
        return $lower <= $v && $v <= $upper;
    }

    /**
     * @param string $delimiter
     * @param string $s
     * @return string[]
     */
    static function explodeNotEmpty(string $delimiter, string $s) {
        $tokens = explode($delimiter, $s);
        if ($tokens === false) {
            throw new \InvalidArgumentException();
        }
        return $tokens;
    }

    /**
     * @param string $s
     * @param string $remove
     * @param string $beforeNeedle
     * @return string
     */
    static function strLastPart(string $s, string $remove = null, string $beforeNeedle = null) {
        $actualBeforeNeedle = $beforeNeedle ?? "\\";
        $actualRemove = $remove ?? "";

        $lastPart = substr(strrchr($s, $actualBeforeNeedle), 1);
        if ($lastPart === false) {
            throw new \InvalidArgumentException();
        }

        $lastPart = str_replace($actualRemove, '', $lastPart);
        return $lastPart;
    }

    /**
     * @param int $n
     * @return string
     */
    static function getNumberSuffix(int $n) {
        if (!Utils::inRange($n, 1, 4)) {
            throw new \InvalidArgumentException('Not implemented.');
        }
        $m = [
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            4 => 'th'
        ];
        return $m[$n];
    }

    /**
     * @param string $prefix
     * @return string
     */
    static function generateRandomToken(string $prefix) {
        return sprintf('%s-%s-%s', $prefix, date('Ymdhis'), static::$tokenNum++);
    }

    /**
     * @param $targetValue
     * @param bool $strict
     * @return \Closure
     */
    static function toPredicate($targetValue, bool $strict = false) {
        if ($strict) {
            return function ($v) use ($targetValue) {
                return $v === $targetValue;
            };
        } else {
            return function ($v) use ($targetValue) {
                return $v == $targetValue;
            };
        }
    }

    /**
     * @param $class
     * @return \Closure
     */
    static function toClassPredicate($class) {
        return function ($v) use ($class) {
            return isset($class)
                ? $v instanceof $class
                : true;
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

    /**
     * @return \Closure
     */
    static function getToStringCallback() {
        /**
         * @param mixed $value
         * @return string
         */
        return function ($value) {
            return is_object($value) ? $value->__toString() : $value;
        };
    }

    /**
     * @param string $method
     * @return \Closure
     */
    static function getMethodCallback(string $method) {
        return function ($value) use ($method) {
            return $value->$method();
        };
    }

    private function __construct() {
    }
}
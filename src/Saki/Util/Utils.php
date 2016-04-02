<?php

namespace Saki\Util;

class Utils {
    static function normalizedMod(int $v, int $n) {
        if ($n < 0) {
            throw new \InvalidArgumentException();
        }
        return $n == 0 ? 0 : ($v % $n + $n) % $n;
    }

    static function getClassNamePart(string $class) {
        // A\B\XXClass -> XXClass
        $lastSeparatorPos = strrpos($class, '\\');
        return substr($class, $lastSeparatorPos + 1);
    }

    static function inRange($v, $lowerLimit, $upperLimit) {
        return $lowerLimit <= $v && $v <= $upperLimit;
    }

    static function explodeSafe(string $delimiter, string $string) {
        $tokens = explode($delimiter, $string);
        if ($tokens === false) {
            throw new \InvalidArgumentException();
        }
        return $tokens;
    }

    static function toPredicate($targetValue, bool $strict = false) {
        return function ($v) use ($targetValue, $strict) {
            return $strict ? $v === $targetValue : $v == $targetValue;
        };
    }

    private function __construct() {

    }
}
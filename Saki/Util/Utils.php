<?php

namespace Saki\Util;

class Utils {
    /**
     * todo bad smell. any better ways?
     * @param array $descBestOnes
     * @return \Closure
     */
    static function getComparatorByBestArray(array $descBestOnes) {
        $comparator = function ($a, $b) use ($descBestOnes) {
            $ia = array_search($a, $descBestOnes);
            $ib = array_search($b, $descBestOnes);
            if ($ia === false || $ib === false) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid compare targetList [%s] and [%s] for $descBestOnes[%s]', $a, $b, implode(',', $descBestOnes))
                );
            }
            if ($ia == $ib) {
                return 0;
            } else {
                return $ia < $ib ? 1 : -1;
            }
        };
        return $comparator;
    }

    static function sgn($n) {
        return $n == 0 ? 0 : ($n > 0 ? 1 : -1);
    }

    static function getNormalizedModValue($v, $n) {
        return (($v) % $n + $n) % $n;
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

    private function __construct() {

    }
}
<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
trait Comparable {
    /**
     * design note: to get short implementation, use "if ($v = $this->a <=> $other->a) return $v; else ...".
     * @param Comparable $other
     * @return int -1|0|1
     */
    abstract function compareTo($other);

    /**
     * @return \Closure
     */
    static function getComparator() {
        /**
         * @param $v1
         * @param $v2
         * @return int
         */
        return function ($v1, $v2) {
            return $v1->compareTo($v2);
        };
    }

    /**
     * @return \Closure
     */
    static function getRevertComparator() {
        /**
         * @param $v1
         * @param $v2
         * @return int
         */
        return function ($v1, $v2) {
            return -$v1->compareTo($v2);
        };
    }
}
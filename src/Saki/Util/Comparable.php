<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
trait Comparable {
    /**
     * design note: to get short implementation, use "if ($v = $this->a <=> $other->a) return $v; else ...".
     * @param $other
     * @return int -1|0|1
     */
    abstract function compareTo($other);

    /**
     * @return \Closure
     */
    static function getComparator() {
        return function ($v1, $v2) {
            return $v1->compareTo($v2);
        };
    }
}
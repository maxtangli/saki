<?php
namespace Saki\Util;

trait Comparable {
    abstract function compareTo($other);

    static function getComparator() {
        return function ($v1, $v2) {
            return $v1->compareTo($v2);
        };
    }
}
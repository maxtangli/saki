<?php

namespace Saki\Util;

trait ComparablePriority {
    use Comparable;

    function compareTo($other) {
        return $this->getPriority() <=> $other->getPriority();
    }

    static function getPrioritySelector() {
        return function ($v) {
            return $v->getPriority();
        };
    }

    abstract function getPriority();
}
<?php

namespace Saki\Util;

trait ComparablePriority { // todo remove
    use Comparable;

    abstract function getPriority();

    function compareTo($other) {
        return $this->getPriority() <=> $other->getPriority();
    }

    static function getPrioritySelector() {
        return function ($v) {
            return $v->getPriority();
        };
    }
}
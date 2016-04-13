<?php

namespace Saki\Util;

trait ComparablePriority { // todo remove?
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
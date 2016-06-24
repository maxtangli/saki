<?php

namespace Saki\Util;

trait ComparablePriority {
    use Comparable;

    /**
     * @param ComparablePriority $other
     * @return bool
     */
    function compareTo($other) {
        return $this->getPriority() <=> $other->getPriority();
    }

    static function getPrioritySelector() {
        /**
         * @param ComparablePriority $v
         * @return int
         */
        return function ($v) {
            return $v->getPriority();
        };
    }

    abstract function getPriority();
}
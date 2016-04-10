<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
trait ComparableSequence {
    use Comparable;

    /**
     * @param $other
     * @return bool
     */
    function isAfter($other) {
        return $this->compareTo($other) > 0;
    }

    /**
     * @param $other
     * @return bool
     */
    function isAfterOrSame($other) {
        return $this->compareTo($other) >= 0;
    }

    /**
     * @param $other
     * @return bool
     */
    function isSame($other) {
        return $this->compareTo($other) == 0;
    }

    /**
     * @param $other
     * @return bool
     */
    function isBefore($other) {
        return $this->compareTo($other) < 0;
    }

    /**
     * @param $other
     * @return bool
     */
    function isBeforeOrSame($other) {
        return $this->compareTo($other) <= 0;
    }
}


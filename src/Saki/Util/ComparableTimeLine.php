<?php
namespace Saki\Util;

trait ComparableTimeLine {
    use Comparable;

    function isLaterThan($other) {
        return $this->compareTo($other) > 0;
    }

    function isLaterThanOrSame($other) {
        return $this->compareTo($other) >= 0;
    }

    function isSameTime($other) {
        return $this->compareTo($other) == 0;
    }

    function isEarlierThan($other) {
        return $this->compareTo($other) < 0;
    }

    function isEarlierThanOrSame($other) {
        return $this->compareTo($other) <= 0;
    }
}
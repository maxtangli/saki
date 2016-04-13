<?php
namespace Saki\Result;

class PointDelta { // todo remove
    private $before;
    private $deltaInt;

    function __construct($before, $deltaInt) {
        $this->before = $before;
        $this->deltaInt = $deltaInt;
    }

    function __toString() {
        if ($this->getDeltaInt() != 0) {
            $mark = $this->getDeltaInt() > 0 ? '+' : '-';
            return sprintf('%s %s %s -> %s', $this->getBefore(), $mark, abs($this->getDeltaInt()), $this->getAfter());
        } else {
            return sprintf('%s -> %s', $this->getBefore(), $this->getAfter());
        }
    }

    function getBefore() {
        return $this->before;
    }

    function getDeltaInt() {
        return $this->deltaInt;
    }

    function getAfter() {
        return $this->getBefore() + $this->getDeltaInt();
    }
}
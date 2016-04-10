<?php
namespace Saki\Util;

trait ComparableQuantity {
    use ComparableSequence;

    function compareTo($other) {
        return $this->getQuantity() <=> $other->getQuantity();
    }

    abstract protected function getQuantity();

    function getOffsetFrom($other) {
        return $this->getQuantity() - $other->getQuantity();
    }

    function getOffsetTo($other) {
        return -$this->getOffsetFrom($other);
    }

    abstract function toNext(int $offset);
}
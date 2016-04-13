<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
trait ComparableIndex {
    use ComparableSequence;

    function compareTo($other) {
        return $this->getIndex() <=> $other->getIndex();
    }

    /**
     * @param $other
     * @return int
     */
    function getOffsetTo($other) {
        return $other->getIndex() - $this->getIndex();
    }

    /**
     * @param int $index
     * @return static
     */
    abstract static function fromIndex(int $index);

    /**
     * @return int
     */
    abstract function getIndex();

    /**
     * @param int $offset
     * @return static
     */
    abstract function toNext(int $offset);
}
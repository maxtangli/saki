<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
trait ComparableIndex {
    use ComparableSequence;

    /**
     * @param ComparableIndex $other
     * @return bool
     */
    function compareTo($other) {
        return $this->getIndex() <=> $other->getIndex();
    }

    /**
     * @param ComparableIndex $other
     * @return int
     */
    function getOffsetTo($other) {
        return $other->getIndex() - $this->getIndex();
    }

    /**
     * @param ComparableIndex $other
     * @param int $n
     * @return int
     */
    function getNormalizedOffsetTo($other, int $n) {
        return Utils::normalizedMod($this->getOffsetTo($other), $n);
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
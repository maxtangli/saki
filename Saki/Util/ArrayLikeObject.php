<?php
namespace Saki\Util;

class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {

    private $innerArray;

    function __construct(array $innerArray) {
        $this->setInnerArray($innerArray);
    }

    /**
     * @return mixed[]
     */
    function toArray() {
        return $this->innerArray;
    }

    protected function setInnerArray($innerArray) {
        $this->innerArray = $innerArray;
    }

    function getIterator() {
        return new \ArrayIterator($this->innerArray);
    }

    function offsetExists($offset) {
        return isset($this->innerArray[$offset]);
    }

    /**
     * @param int $offset
     * @return mixed
     */
    function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->innerArray[$offset];
        } else {
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for \$innerArray[$this->innerArray].");
        }
    }

    function offsetSet($offset, $value) {
        throw new \BadMethodCallException();
    }

    function offsetUnset($offset) {
        throw new \BadMethodCallException();
    }

    function count() {
        return count($this->innerArray);
    }
}
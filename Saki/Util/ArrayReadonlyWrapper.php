<?php
namespace Saki\Util;

class ArrayReadonlyWrapper implements \IteratorAggregate, \Countable, \ArrayAccess {

    private $innerArray;

    function __construct(array $innerArray) {
        $this->innerArray = $innerArray;
    }

    protected function getInnerArray() {
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

    function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->innerArray[$offset];
        } else {
            throw new \InvalidArgumentException();
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
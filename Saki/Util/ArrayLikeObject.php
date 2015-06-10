<?php
namespace Saki\Util;

/**
 * convenient base class for ascend-number-key array like object.
 * @package Saki\Util
 */
class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {

    private $innerArray;

    function __construct(array $innerArray) {
        $validArray = empty($innerArray) || array_keys($innerArray) === range(0, count($innerArray) - 1);
        if (!$validArray) {
            $innerArrayString = implode(',', $innerArray);
            throw new \InvalidArgumentException("Invalid \$innerArray[$innerArrayString], ascend-number-key array expected.");
        }
        $this->setInnerArray($innerArray);
    }

    function __toString() {
        return implode(',', $this->innerArray);
    }

    /**
     * @return mixed[]
     */
    function toArray() {
        return $this->innerArray;
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
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayLikeObject \$this[$this].");
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

    protected function toFirstIndex($targetItem, $strict = false) {
        $i = array_search($targetItem, $this->innerArray, $strict);
        if ($i === false) {
            throw new \InvalidArgumentException("Invalid target \$targetItem[$targetItem] for ArrayLikeObject \$this[$this] by \$strict[$strict].");
        }
        return $i;
    }

    protected function setInnerArray($innerArray, $callHook = true) {
        $this->innerArray = $innerArray;
        if ($callHook) {
            $this->innerArrayChangedHook();
        }
    }

    protected function assertValidPositions($posOrPositions) {
        $positions = is_array($posOrPositions) ? $posOrPositions : [$posOrPositions];
        foreach($positions as $pos) {
            $validPos = 0 <= $pos && $pos <= $this->count();
            if (!$validPos) {
                throw new \InvalidArgumentException();
            }
        }
    }

    protected function insert($item, $pos = null) {
        $this->insertMany([$item], $pos);
    }

    protected function insertMany(array $items, $pos = null) {
        $actualPos = $pos !== null ? $pos : $this->count();
        $this->assertValidPositions($actualPos);
        array_splice($this->innerArray, $actualPos, 0, $items);
        $this->innerArrayChangedHook();
    }

    protected function push($item) {
        $this->insert($item, $this->count());
    }

    protected function pushMany(array $items) {
        $this->insertMany($items, $this->count());
    }

    protected function unshift($item) {
        $this->insert($item, 0);
    }

    protected function unshiftMany(array $items) {
        $this->insertMany($items, 0);
    }

    protected function replace($pos, $obj) {
        $this->assertValidPositions($pos);
        $ret = $this->innerArray[$pos];
        $this->innerArray[$pos] = $obj;
        $this->innerArrayChangedHook();
        return $ret;
    }

    protected function remove($pos) {
        $this->assertValidPositions($pos);
        $ret = $this->innerArray[$pos];
        array_splice($this->innerArray, $pos, 1);
        $this->innerArrayChangedHook();
        return $ret;
    }

    protected function removeMany(array $positions) {
        $this->assertValidPositions($positions);
        $innerArray = $this->innerArray;
        $removedItems = array_map(function($pos)use($innerArray){return $innerArray[$pos];}, $positions);
        $newItems = [];
        foreach ($innerArray as $pos => $item) {
            if (!in_array($pos, $positions)) {
                $newItems[] = $item;
            }
        }
        $this->setInnerArray($newItems);
        return $removedItems;
    }

    protected function pop() {
        return $this->remove($this->count() - 1);
    }

    protected function popMany($n) {
        if ($n > $this->count()) {
            throw new \InvalidArgumentException();
        }
        $high = $this->count() - 1;
        $low = $high - $n + 1;
        $positions = range($high, $low, -1);
        return $this->removeMany($positions);
    }

    protected function shift() {
        return $this->remove(0);
    }

    protected function shiftMany($n) {
        if ($n > $this->count()) {
            throw new \InvalidArgumentException();
        }
        $positions = range(0, $n - 1, 1);
        return $this->removeMany($positions);
    }

    protected function innerArrayChangedHook() {

    }
}
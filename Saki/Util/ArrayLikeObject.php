<?php
namespace Saki\Util;

/**
 * Convenient base class for 0-begin-continuous-ascending-int-key-array like object.
 * Only optimized for less-than-200 elements case, which is the main case in pj saki.
 * @package Saki\Util
 */
class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {
    /**
     * @param mixed|array $valueOrValues
     * @return array
     */
    static function boxing($valueOrValues) {
        return is_array($valueOrValues) ? array_values($valueOrValues) : [$valueOrValues];
    }

    /**
     * @param mixed|array $valueOrValues
     * @return mixed|array
     */
    static function unboxing($valueOrValues) {
        if (is_array($valueOrValues)) {
            return count($valueOrValues) > 1 ? $valueOrValues : $valueOrValues[0];
        } else {
            return $valueOrValues;
        }
    }

    /**
     * For $originIsArray case, unpack 1-element-array to single element.
     * Otherwise keep.
     * @param array $values
     * @param bool $originIsArray
     * @return mixed|array
     */
    protected static function unboxingByOrigin(array $values, $originIsArray) {
        $valid = !$originIsArray && count($values) != 1;
        if ($valid) {
            throw new \InvalidArgumentException('');
        }
        return $originIsArray ? $values : $values[0];
    }

    /**
     * @param null|bool|object|callable $equals
     * @return callable
     */
    protected static function toEquals($equals) {
        if (is_null($equals)) {
            return function ($a, $b) {
                return $a == $b;
            };
        } elseif (is_callable($equals)) {
            return $equals;
        } elseif (is_bool($equals)) {
            $isStrict = $equals;
            if ($isStrict) {
                return function ($a, $b) {
                    return $a === $b;
                };
            } else {
                return function ($a, $b) {
                    return $a == $b;
                };
            }
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param null|array|callable $comparator
     * @param bool $isAsc
     * @return \Closure with signature: -1|0|1 function($a, $b)
     */
    protected static function toComparator($comparator = null, $isAsc = true) {
        if (is_array($comparator)) {
            $s = Utils::getComparatorByBestArray($comparator);
        } else {
            $s = $comparator ?: function ($a, $b) {
                if ($a == $b) {
                    return 0;
                } else {
                    return $a > $b ? 1 : -1;
                }
            };
        }

        return function ($a, $b) use ($s, $isAsc) {
            return $isAsc ? $s($a, $b) : -$s($a, $b);
        };
    }

    private $innerArray;
    private $writable;

    function __construct(array $innerArray, $writable = true) {
        $this->writable = true; // to allow setInnerArray()
        $this->setInnerArray($innerArray); // to trigger OnInnerArrayChanged()
        $this->writable = $writable;
    }

    function reset() {
        $this->innerArray = [];
    }

    function __toString() {
        $glue = ',';
        return implode($glue, $this->innerArray);
    }

    function isWritable() {
        return $this->writable;
    }

    function setWritable($writable) {
        $this->writable = $writable;
    }

    protected function assertWritable() {
        if (!$this->isWritable()) {
            throw new \LogicException('The ArrayLikeObject[%s] is not writable.', $this->__toString());
        }
    }

    // convert

    function toArray(callable $selector = null) {
        if ($selector !== null) {
            $r = [];
            foreach ($this as $v) {
                $r[] = $selector($v);
            }
            return $r;
        } else {
            return $this->innerArray;
        }
    }

    function toFilteredArray(callable $predicate) {
        return array_values(array_filter($this->innerArray, $predicate));
    }

    function toReducedValue(callable $reduceCallback, $initialValue) {
        return array_reduce($this->toArray(), $reduceCallback, $initialValue);
    }

    // implement interfaces

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
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayLikeObject \$this[$this].");
        }
    }

    function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            $this->innerArray[$offset] = $value;
            $this->onInnerArrayChanged();
        } else {
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayLikeObject \$this[$this].");
        }
    }

    function offsetUnset($offset) {
        // NOTE: not supported since some of usages like unset($k1, $k2) would be buggy because of index-rearrange comes after modify
        throw new \BadMethodCallException();
    }

    function count() {
        return count($this->innerArray);
    }

    function isEmpty() {
        return $this->count() == 0;
    }

    // read operations: index

    /**
     * @param int|int[] $indexOrIndexes not allow duplicate
     * @return bool
     */
    function indexExist($indexOrIndexes) {
        if (!$this->util_isUniqueIndexOrIndexes($indexOrIndexes)) {
            throw new \InvalidArgumentException(
                sprintf('$indexOrIndexes[%s] should be .', implode(',', $this->boxing($indexOrIndexes)))
            );
        }

        $indexes = static::boxing($indexOrIndexes);
        foreach ($indexes as $i) {
            if (!$this->offsetExists($i)) {
                return false;
            }
        }
        return true;
    }

    protected function util_isUniqueIndexOrIndexes($indexOrIndexes) {
        return !is_array($indexOrIndexes) || (array_unique($indexOrIndexes) == $indexOrIndexes);
    }

    /**
     * @param int|int[] $indexOrIndexes not allow duplicate
     * @return mixed|array
     */
    function indexToValue($indexOrIndexes) {
        if (!$this->indexExist($indexOrIndexes)) {
            throw new \InvalidArgumentException("Invalid \$indexOrIndexes[$indexOrIndexes] for " . __CLASS__ . " \$this[$this]");
        }

        $indexes = static::boxing($indexOrIndexes);
        $values = array_map(function ($i) {
            return $this[$i];
        }, $indexes);
        $valueOrValues = static::unboxingByOrigin($values, is_array($indexOrIndexes));
        return $valueOrValues;
    }



    // read operations: value

    /**
     * @param object|array $valueOrValues
     * @param null|bool|object|callable $equals
     * @return bool whether all $valueOrValues existed or not, where duplicate values should exist for multiple times.
     */
    function valueExist($valueOrValues, $equals = null) {
        try {
            $this->valueToIndex($valueOrValues, $equals);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param object|array $valueOrValues
     * @param null|bool|object|callable $equals
     * @return int|int[] all $valueOrValues' indexes, where duplicate values should exist for multiple times.
     */
    function valueToIndex($valueOrValues, $equals = null) {
        // prepare data
        $targets = [];
        foreach(static::boxing($valueOrValues) as $iTarget => $value) {
            $targets[$iTarget]['tobeFoundValue'] = $value;
            $targets[$iTarget]['foundIndex'] = false;
        }
        $tobeFoundCount = count($targets);
        $actualEquals = $this->toEquals($equals);

        // loop to find
        foreach($this->innerArray as $i => $v) {
            // if this $v is not found target, record it
            foreach ($targets as $k => $target) {
                $notFoundYet = $target['foundIndex'] === false;
                if ($notFoundYet && $actualEquals($v, $target['tobeFoundValue'])) {
                    $targets[$k]['foundIndex'] = $i;
                    --$tobeFoundCount;
                    break;
                }
            }

            if ($tobeFoundCount <= 0) {
                break;
            }
        }

        // judge result
        $allFound = $tobeFoundCount == 0;
        if (!$allFound) {
            throw new \InvalidArgumentException(
                sprintf('Unable to find $valueOrValues[%s] in %s[%s]', implode(',', self::boxing($valueOrValues)), __CLASS__, $this->__toString())
            );
        }

        $foundIndexes = array_map(function ($target) {
            return $target['foundIndex'];
        }, $targets);
        $foundIndexOrIndexes = static::unboxingByOrigin($foundIndexes, is_array($valueOrValues));
        return $foundIndexOrIndexes;
    }

    function getFirst() {
        return $this->indexToValue(0);
    }

    function getNext($originValue, $offset = 1) {
        $originIndex = $this->valueToIndex($originValue);
        $targetIndex = Utils::getNormalizedModValue($originIndex + $offset, $this->count());
        $targetValue = $this->indexToValue($targetIndex);
        return $targetValue;
    }

    function getLast() {
        return $this->indexToValue($this->count() - 1);
    }

    /**
     * @param null|array|callable $comparator
     * @return object
     */
    function getMin($comparator = null) {
        $maxComparator = $this->toComparator($comparator, false);
        return $this->getMax($maxComparator);
    }

    /**
     * @param null|array|callable $comparator
     * @return object
     */
    function getMax($comparator = null) {
        if ($this->count() == 0) {
            throw new \InvalidArgumentException();
        }

        $actualComparator = $this->toComparator($comparator);
        $result = $this->innerArray[0];
        foreach ($this->innerArray as $v) {
            if ($actualComparator($v, $result) > 0) {
                $result = $v;
            }
        }
        return $result;
    }

    /**
     * @param object $value
     * @param null|bool|object|callable $equals
     * @return int
     */
    function getEqualValueCount($value, $equals = null) {
        $actualEquals = $this->toEquals($equals);
        $count = 0;
        foreach ($this->innerArray as $m) {
            if ($actualEquals($value, $m)) {
                ++$count;
            }
        }
        return $count;
    }

    function getFilteredValueCount(callable $predicate) {
        return count($this->toFilteredArray($predicate));
    }

    function any(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == true) {
                return true;
            }
        }
        return false;
    }

    function all(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == false) {
                return false;
            }
        }
        return true;
    }

    // write operations: special

    function setInnerArray(array $innerArray) {
        $this->assertWritable();

        if (!$this->util_is0BeginAscendingIntKeyArray($innerArray)) {
            throw new \InvalidArgumentException(
                sprintf('$innerArray[%s] should be 0-begin-ascending-int-key-array.', implode(',', $innerArray))
            );
        }
        $this->innerArray = $innerArray;
        $this->onInnerArrayChanged();
    }

    protected function util_is0BeginAscendingIntKeyArray(array $a) {
        return empty($a) || array_keys($a) === range(0, count($a) - 1);
    }

    function walk(callable $callback) {
        $this->assertWritable();

        array_walk($this->innerArray, $callback);
        $this->onInnerArrayChanged();
    }

    function shuffle() {
        $this->assertWritable();

        shuffle($this->innerArray);
        $this->onInnerArrayChanged();
    }

    function leftShift($n) {
        $this->assertWritable();

        $count = $this->count();
        if ($n == 0 || $count <= 1) {
            return;
        }

        $leftShiftCount = Utils::getNormalizedModValue($n, $count);
        if ($leftShiftCount == 0) {
            return;
        }

        $newRight = array_slice($this->innerArray, 0, $leftShiftCount);
        $newLeft = array_slice($this->innerArray, $leftShiftCount);
        $newAll = array_merge($newLeft, $newRight);
        $this->innerArray = $newAll;

        $this->onInnerArrayChanged();
    }

    function rightShift($n) {
        $this->assertWritable();

        $this->leftShift(-$n);
        // onInnerArrayChanged() already called in leftShift().
    }

    /**
     * @param null|bool|object|callable $equals
     */
    function unique(callable $equals = null) {
        $this->assertWritable();

        if ($equals !== null) {
            $result = new ArrayLikeObject([]);
            foreach ($this as $target) {
                if (!$result->valueExist($target, $equals)) {
                    $result->push($target);
                }
            }
            $this->innerArray = $result->toArray();
        } else {
            $this->innerArray = array_unique($this->innerArray);
        }
        $this->onInnerArrayChanged();
    }

    // write operations: replace

    function replaceByIndex($indexOrIndexes, $newValueOrValues) {
        $this->assertWritable();

        $validIndexes = $this->indexExist($indexOrIndexes);
        if (!$validIndexes) {
            throw new \InvalidArgumentException();
        }

        list($indexes, $newValues) = [static::boxing($indexOrIndexes), static::boxing($newValueOrValues)];
        $validNewValues = count($indexes) == count($newValues);
        if (!$validNewValues) {
            throw new \InvalidArgumentException();
        }

        $replace = array_combine($indexes, $newValues);
        $this->innerArray = array_replace($this->innerArray, $replace);
        $this->onInnerArrayChanged();
    }

    /**
     * @param object|array $oldValueOrValues
     * @param object|array $newValueOrValues
     * @param null|bool|object|callable $equals
     */
    function replaceByValue($oldValueOrValues, $newValueOrValues, $equals = null) {
        $this->assertWritable();

        $indexOrIndexes = $this->valueToIndex($oldValueOrValues, $equals);
        $this->replaceByIndex($indexOrIndexes, $newValueOrValues);
        // onInnerArrayChanged() already called in replaceByIndex().
    }

    // write operations: insert

    function insert($valueOrValues, $pos) {
        $this->assertWritable();

        $valid = 0 <= $pos && $pos <= $this->count();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $values = static::boxing($valueOrValues);
        array_splice($this->innerArray, $pos, 0, $values); // splice() will rearrange integer-keys
        $this->onInnerArrayChanged();
    }

    function unShift($valueOrValues) {
        $this->assertWritable();

        $this->insert($valueOrValues, 0);
        // onInnerArrayChanged() already called in insert().
    }

    function push($valueOrValues) {
        $this->assertWritable();

        $this->insert($valueOrValues, $this->count());
        // onInnerArrayChanged() already called in insert().
    }

    function merge(ArrayLikeObject $otherList) {
        $this->assertWritable();

        $this->insert($otherList->toArray(), $this->count());
        // onInnerArrayChanged() already called in insert().
    }

    // write operations: remove

    function removeByIndex($indexOrIndexes) {
        $this->assertWritable();

        $removedValues = $this->indexToValue($indexOrIndexes); // valid check

        $tobeRemovedIndexes = static::boxing($indexOrIndexes);
        $newInnerArray = [];
        foreach ($this->innerArray as $k => $v) {
            if (!in_array($k, $tobeRemovedIndexes)) {
                $newInnerArray[] = $v;
            }
        }
        $this->innerArray = $newInnerArray;
        $this->onInnerArrayChanged();

        return $removedValues;
    }

    /**
     * @param object|array $valueOrValues
     * @param null|bool|object|callable $equals
     * @return object|array removed value or values.
     */
    function removeByValue($valueOrValues, $equals = null) {
        $this->assertWritable();

        return $this->removeByIndex($this->valueToIndex($valueOrValues, $equals));
    }

    function shift($n = 1) {
        $this->assertWritable();

        $indexOrIndexes = $n == 1 ? 0 : range(0, $n - 1);
        return $this->removeByIndex($indexOrIndexes);
    }

    function pop($n = 1) {
        $this->assertWritable();

        $count = $this->count();
        if (!(0 < $n && $n <= $count)) {
            throw new \InvalidArgumentException(
                sprintf('pop count $n[%s] should less than total count[%s].', $n, $count)
            );
        }

        $reversedResult = array_splice($this->innerArray, $count - $n, $n);
        $this->onInnerArrayChanged();

        $result = array_reverse($reversedResult, false);
        return self::unboxingByOrigin($result, $n > 1);
    }

    // hook

    private $allowHook = true;

    protected final function onInnerArrayChanged() {
        if ($this->allowHook) {
            $this->allowHook = false;
            try {
                $this->innerArrayChangedHook();
            } finally {
                $this->allowHook = true;
            }
        }
    }

    protected function innerArrayChangedHook() {

    }
}
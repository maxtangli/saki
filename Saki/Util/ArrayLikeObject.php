<?php
namespace Saki\Util;

/**
 * Convenient base class for 0-begin-continuous-ascending-int-key-array like object.
 * Only optimized for less-than-200 elements case.
 * @package Saki\Util
 */
class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {
    protected static function is0BeginAscendingIntKeyArray(array $a) {
        return empty($a) || array_keys($a) === range(0, count($a) - 1);
    }

    /**
     * @param $valueOrValues
     * @return array
     */
    static function boxing($valueOrValues) {
        return is_array($valueOrValues) ? array_values($valueOrValues) : [$valueOrValues];
    }

    /**
     * @param $valueOrValues
     * @return object|array
     */
    static function unboxing($valueOrValues) {
        if (is_array($valueOrValues)) {
            return count($valueOrValues) > 1 ? $valueOrValues : $valueOrValues[0];
        } else {
            return $valueOrValues;
        }
    }

    // todo bad smell. originIsArray means count()>1 array?
    protected static function unboxingByOrigin(array $values, $originIsArray) {
        $valid = !$originIsArray && count($values) != 1;
        if ($valid) {
            throw new \InvalidArgumentException('');
        }
        return $originIsArray ? $values : $values[0];
    }

    protected static function isUniqueArray(array $a) {
        return array_unique($a) == $a;
    }

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
     * @param callable|null|array $comparator
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

    function __construct(array $innerArray) {
        $this->setInnerArray($innerArray);
    }

    function reset() {
        $this->innerArray = [];
    }

    function __toString() {
        return $this->toString(',');
    }

    function toString($glue) {
        return implode($glue, $this->innerArray);
    }

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

    function isNotEmpty() {
        return $this->count() > 0;
    }

    function setInnerArray(array $innerArray) {
        if (!static::is0BeginAscendingIntKeyArray($innerArray)) {
            $innerArrayString = implode(',', $innerArray);
            throw new \InvalidArgumentException("Invalid \$innerArray[$innerArrayString], 0-begin-ascending-int-key-array expected.");
        }
        $this->innerArray = $innerArray;
        $this->onInnerArrayChanged();
    }

    function all(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == false) {
                return false;
            }
        }
        return true;
    }

    function any(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == true) {
                return true;
            }
        }
        return false;
    }

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

    function getMin($comparator = null) {
        $maxComparator = $this->toComparator($comparator, false);
        return $this->getMax($maxComparator);
    }

    function walk(callable $callback) {
        foreach ($this as $v) {
            $callback($v);
        }
        // do not call onInnerArrayChanged since innerArray k-v relation not changed
    }

    function unique(callable $equals = null) {
        if ($equals !== null) {
            $result = new ArrayLikeObject([]);
            foreach($this as $target) {
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

    function toReducedValue(callable $reduceCallback, $initial) {
        return array_reduce($this->toArray(), $reduceCallback, $initial);
    }

    /**
     * @param mixed|array $valueOrValues
     * @param null|bool|callable $equals
     * @return int|int[] non-duplicate first indexes of $targetItems
     */
    function valueToIndex($valueOrValues, $equals = null) {
        $targets = static::boxing($valueOrValues);
        $actualEquals = $this->toEquals($equals);

        $tobeFoundCount = count($targets);
        $foundIndexes = array_fill(0, $tobeFoundCount, false);
        $innerArray = $this->innerArray;
        $innerArrayCount = count($innerArray);

        for ($i = 0; $i < $innerArrayCount && $tobeFoundCount > 0; ++$i) {
            $v = $innerArray[$i];

            foreach ($targets as $k => $target) {
                $alreadyFound = $foundIndexes[$k] !== false;
                if (!$alreadyFound && $actualEquals($target, $v)) {
                    $foundIndexes[$k] = $i;
                    --$tobeFoundCount;
                    break;
                }
            }
        }

        $allFound = $tobeFoundCount == 0;
        if (!$allFound) {
            $valueOrValuesString = is_array($valueOrValues) ? implode(',', $valueOrValues) : $valueOrValues;
            throw new \InvalidArgumentException(
                "Invalid \$valueOrValues[$valueOrValuesString] for " . __CLASS__ . " \$this[$this]."
            );
        }

        $foundIndexOrIndexes = static::unboxingByOrigin($foundIndexes, is_array($valueOrValues));
        return $foundIndexOrIndexes;
    }

    function valueExist($valueOrValues, $equals = null) {
        try {
            $this->valueToIndex($valueOrValues, $equals);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    function getValueCount($value, $equals = null) {
        $actualEquals = $this->toEquals($equals);
        $count = 0;
        foreach ($this->innerArray as $m) {
            if ($actualEquals($value, $m)) {
                ++$count;
            }
        }
        return $count;
    }

    function getMatchedValueCount(callable $predicate) {
        return count($this->toFilteredArray($predicate));
    }

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

    function indexExist($indexOrIndexes) {
        $indexes = static::boxing($indexOrIndexes);
        foreach ($indexes as $i) {
            if (!$this->offsetExists($i)) {
                return false;
            }
        }
        return true;
    }

    function getNext($originValue, $offset = 1) {
        $originIndex = $this->valueToIndex($originValue);
        $targetIndex = $this->util_getNormalizedModValue($originIndex + $offset, $this->count());
        $targetValue = $this->indexToValue($targetIndex);
        return $targetValue;
    }

    private function util_getNormalizedModValue($v, $n) {
        return (($v) % $n + $n) % $n;
    }

    /**
     * @return mixed
     */
    function getFirst() {
        return $this->indexToValue(0);
    }

    /**
     * @return mixed
     */
    function getLast() {
        return $this->indexToValue($this->count() - 1);
    }

    function replaceByIndex($indexOrIndexes, $newValueOrValues) {
        $indexes = static::boxing($indexOrIndexes);
        $newValues = static::boxing($newValueOrValues);
        $valid = $this->indexExist($indexOrIndexes) && static::isUniqueArray($indexes) && count($indexes) == count($newValues);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $replace = array_combine($indexes, $newValues);
        $this->innerArray = array_replace($this->innerArray, $replace);
        $this->onInnerArrayChanged();
    }

    function replaceByValue($oldValueOrValues, $newValueOrValues, $equals = null) {
        $indexOrIndexes = $this->valueToIndex($oldValueOrValues, $equals);
        $this->replaceByIndex($indexOrIndexes, $newValueOrValues);
    }

    function insert($valueOrValues, $pos) {
        $valid = is_int($pos) && $pos == $this->count() || $this->indexExist($pos);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $values = static::boxing($valueOrValues);
        array_splice($this->innerArray, $pos, 0, $values); // splice() will rearrange integer-keys
        $this->onInnerArrayChanged();
    }

    function unShift($valueOrValues) {
        $this->insert($valueOrValues, 0);
    }

    function push($valueOrValues) {
        $this->insert($valueOrValues, $this->count());
    }

    function removeByIndex($indexOrIndexes) {
        $valid = !is_array($indexOrIndexes) || empty($indexOrIndexes) ||
            (array_unique($indexOrIndexes) == $indexOrIndexes
                && isset($this->innerArray[is_array($indexOrIndexes) ? max($indexOrIndexes) : $indexOrIndexes]));
        if (!$valid) {
            throw new \InvalidArgumentException($this);
        }

        $removedValues = $this->indexToValue($indexOrIndexes);

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

    function removeByValue($valueOrValues, $equals = null) {
        return $this->removeByIndex($this->valueToIndex($valueOrValues, $equals));
    }

    function shift($n = 1) {
        $indexOrIndexes = $n == 1 ? 0 : range(0, $n - 1);
        return $this->removeByIndex($indexOrIndexes);
    }

    function pop($n = 1) {
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

    function merge(ArrayLikeObject $otherList) {
        $this->push($otherList->toArray());
    }

    function shuffle() {
        shuffle($this->innerArray);
        $this->onInnerArrayChanged();
    }

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
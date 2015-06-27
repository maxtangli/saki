<?php
namespace Saki\Util;

/**
 * convenient base class for 0-begin-ascending-int-key-array like object.
 * @package Saki\Util
 */
class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {
    /**
     * @param array $a
     * @return bool
     */
    final static function is0BeginAscendingIntKeyArray(array $a) {
        return empty($a) || array_keys($a) === range(0, count($a) - 1);
    }

    /**
     * @param mixed|array $valueOrValues
     * @return array
     */
    final protected static function boxing($valueOrValues) {
        return is_array($valueOrValues) ? array_values($valueOrValues) : [$valueOrValues];
    }

    /**
     * @param array $values
     * @param bool $originIsArray
     * @return mixed|array valueOrValues
     */
    final protected static function unboxing(array $values, $originIsArray) {
        if (!$originIsArray && count($values) != 1) {
            throw new \InvalidArgumentException();
        }
        return $originIsArray ? $values : $values[0];
    }

    final protected static function isUniqueArray(array $a) {
        return array_unique($a) == $a;
    }

    private $innerArray;

    /**
     * @param array $innerArray
     */
    function __construct(array $innerArray) {
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

    /**
     * @return ArrayIterator
     */
    function getIterator() {
        return new \ArrayIterator($this->innerArray);
    }

    /**
     * @param int $offset
     * @return bool
     */
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

    /**
     * @param int $offset
     * @param mixed $value
     */
    function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            $this->innerArray[$offset] = $value;
            $this->onInnerArrayChanged();
        } else {
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayLikeObject \$this[$this].");
        }
    }

    /**
     * @param int $offset
     */
    function offsetUnset($offset) {
        // NOTE: not supported since some of usages like unset($k1, $k2) would be buggy because of index-rearrange comes after modify
        throw new \BadMethodCallException();
    }

    /**
     * @return int
     */
    function count() {
        return count($this->innerArray);
    }

    function setInnerArray(array $innerArray) {
        if (!static::is0BeginAscendingIntKeyArray($innerArray)) {
            $innerArrayString = implode(',', $innerArray);
            throw new \InvalidArgumentException("Invalid \$innerArray[$innerArrayString], 0-begin-ascending-int-key-array expected.");
        }
        $this->innerArray = $innerArray;
        $this->onInnerArrayChanged();
    }

    function isAll(callable $predicate) {
        return Utils::array_all($this->toArray(), $predicate);
    }

    function isAny(callable $predicate) {
        return Utils::array_any($this->toArray(), $predicate);
    }

    /**
     * @param mixed|array $valueOrValues
     * @param bool $strict
     * @return int|int[] non-duplicate first indexes of $targetItems
     */
    function valueToIndex($valueOrValues, $strict = false) {
        $targets = static::boxing($valueOrValues);
        $equals = function ($v1, $v2) use ($strict) {
            return $strict ? $v1 === $v2 : $v1 == $v2;
        };

        $tobeFoundCount = count($targets);
        $foundIndexes = array_fill(0, $tobeFoundCount, false);
        $innerArray = $this->innerArray;
        $innerArrayCount = count($innerArray);

        for ($i = 0; $i < $innerArrayCount && $tobeFoundCount > 0; ++$i) {
            $v = $innerArray[$i];

            foreach ($targets as $k => $target) {
                $alreadyFound = $foundIndexes[$k] !== false;
                if (!$alreadyFound && $equals($target, $v)) {
                    $foundIndexes[$k] = $i;
                    --$tobeFoundCount;
                    break;
                }
            }
        }

        $allFound = $tobeFoundCount == 0;
        if (!$allFound) {
            $valueOrValuesString = is_array($valueOrValues) ? implode(',', $valueOrValues) : $valueOrValues;
            throw new \InvalidArgumentException("Invalid \$valueOrValues[$valueOrValuesString] for " . __CLASS__ . " \$this[$this] by \$strict[$strict].");
        }

        $foundIndexOrIndexes = static::unboxing($foundIndexes, is_array($valueOrValues));
        return $foundIndexOrIndexes;
    }

    /**
     * @param mixed|array $valueOrValues
     * @param bool $strict
     * @return bool
     */
    function valueExist($valueOrValues, $strict = false) {
        try {
            $this->valueToIndex($valueOrValues, $strict);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed|array $indexOrIndexes
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
        $valueOrValues = static::unboxing($values, is_array($indexOrIndexes));
        return $valueOrValues;
    }

    /**
     * @param mixed|array $indexOrIndexes
     * @return bool
     */
    function indexExist($indexOrIndexes) {
        $indexes = static::boxing($indexOrIndexes);
        foreach ($indexes as $i) {
            if (!$this->offsetExists($i)) {
                return false;
            }
        }
        return true;
    }

    function getFirst() {
        return $this->indexToValue(0);
    }

    function getLast() {
        return $this->indexToValue($this->count()-1);
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

    function replaceByValue($oldValueOrValues, $newValueOrValues, $strict = false) {
        $indexOrIndexes = $this->valueToIndex($oldValueOrValues, $strict);
        $this->replaceByIndex($indexOrIndexes, $newValueOrValues);
    }

    /**
     * @param mixed|array $valueOrValues
     * @param int $pos
     */
    function insert($valueOrValues, $pos) {
        $valid = is_int($pos) && $pos == $this->count() || $this->indexExist($pos);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $values = static::boxing($valueOrValues);
        array_splice($this->innerArray, $pos, 0, $values); // splice() will rearrange integer-keys
        $this->onInnerArrayChanged();
    }

    /**
     * @param mixed|array $valueOrValues
     */
    function unShift($valueOrValues) {
        $this->insert($valueOrValues, 0);
    }

    /**
     * @param mixed|array $valueOrValues
     */
    function push($valueOrValues) {
        $this->insert($valueOrValues, $this->count());
    }

    function removeByIndex($indexOrIndexes) {
        $valid = !is_array($indexOrIndexes) || array_unique($indexOrIndexes)==$indexOrIndexes;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $ret = $this->indexToValue($indexOrIndexes);
        $tobeRemovedIndexes = static::boxing($indexOrIndexes);

        $filtered = array_filter($this->innerArray, function ($v, $k) use ($tobeRemovedIndexes) {
            return array_search($k, $tobeRemovedIndexes) === false;
        }, ARRAY_FILTER_USE_BOTH);
        $this->innerArray = array_values($filtered);
        $this->onInnerArrayChanged();
        return $ret;
    }

    function removeByValue($valueOrValues) {
        return $this->removeByIndex($this->valueToIndex($valueOrValues));
    }

    function shift($n = 1) {
        $indexOrIndexes = $n == 1 ? 0 : range(0, $n - 1);
        return $this->removeByIndex($indexOrIndexes);
    }

    function pop($n = 1) {
        $count = $this->count();
        $last = $count - 1;
        $first = $last - $n + 1;
        $indexOrIndexes = $n == 1 ? $last : range($last, $first);
        return $this->removeByIndex($indexOrIndexes);
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
<?php
namespace Saki\Util;

/**
 * convenient base class for 0-begin-ascending-int-key-array like object.
 * @package Saki\Util
 */
class ArrayLikeObject implements \IteratorAggregate, \Countable, \ArrayAccess {
    final static function is0BeginAscendingIntKeyArray(array $a) {
        return empty($a) || array_keys($a) === range(0, count($a) - 1);
    }

    final protected static function boxing($valueOrValues) {
        return is_array($valueOrValues) ? array_values($valueOrValues) : [$valueOrValues];
    }

    final protected static function unboxing(array $values, $originIsArray) {
        if (!$originIsArray && count($values) != 1) {
            throw new \InvalidArgumentException();
        }
        return $originIsArray ? $values : $values[0];
    }

    final protected static function isUniqueArray(array $a) {
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

    private $innerArray;

    function __construct(array $innerArray) {
        $this->setInnerArray($innerArray);
    }

    function __toString() {
        return implode(',', $this->innerArray);
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

    function setInnerArray(array $innerArray) {
        if (!static::is0BeginAscendingIntKeyArray($innerArray)) {
            $innerArrayString = implode(',', $innerArray);
            throw new \InvalidArgumentException("Invalid \$innerArray[$innerArrayString], 0-begin-ascending-int-key-array expected.");
        }
        $this->innerArray = $innerArray;
        $this->onInnerArrayChanged();
    }

    function all(callable $predicate) {
        return Utils::array_all($this->toArray(), $predicate);
    }

    function any(callable $predicate) {
        return Utils::array_any($this->toArray(), $predicate);
    }

    function walk(callable $callback) {
        foreach ($this as $v) {
            $callback($v);
        }
        // do not call onInnerArrayChanged since innerArray k-v relation not changed
    }

    function unique($equals = null) {
        if ($equals !== null) {
            throw new \InvalidArgumentException('to be implemented.');
        }
        $this->innerArray = array_unique($this->innerArray);
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
            throw new \InvalidArgumentException("Invalid \$valueOrValues[$valueOrValuesString] for " . __CLASS__ . " \$this[$this] by \$equals[$equals].");
        }

        $foundIndexOrIndexes = static::unboxing($foundIndexes, is_array($valueOrValues));
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
        $actualEquals = function ($v1, $v2) use ($equals) {
            return $equals ? $v1 === $v2 : $v1 == $v2;
        };
        $count = 0;
        foreach ($this->innerArray as $m) {
            if ($actualEquals($value, $m)) {
                ++$count;
            }
        }
        return $count;
    }

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
        $valid = !is_array($indexOrIndexes) || array_unique($indexOrIndexes) == $indexOrIndexes;
        $valid = $valid && isset($this->innerArray[is_array($indexOrIndexes) ? max($indexOrIndexes) : $indexOrIndexes]);
        if (!$valid) {
            throw new \InvalidArgumentException();
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
        $last = $count - 1;
        $first = $last - $n + 1;
        $indexOrIndexes = $n == 1 ? $last : range($last, $first);
        return $this->removeByIndex($indexOrIndexes);
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
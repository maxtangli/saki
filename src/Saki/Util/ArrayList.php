<?php
namespace Saki\Util;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * Common index array like C# ArrayList.
 * No optimization, since in pj saki no large size list exist.
 *
 * features
 * - support foreach/count/isset/get/set.
 * - support object-style and clean array operations.
 * - support readonly?
 * - not support array elements.
 *
 * main functions
 * - convert from array: new(array), fromArray(array).
 * - convert to array: toArray.
 * - convert from ArrayList, SomeList: fromSelected($list, $selector).
 * - convert to ArrayList: toArrayList($selector)
 * - convert to SomeList: getCopy, toZipped($list, $resultSelector)
 * - property: count, getCount($predicate).
 *             indexExist($indexes), getValuesAt(indexes), valueExist($values, $equal).
 *             getAggregated($list, $accumulator). getSum($selector), getMin($selector), getMax($selector).
 *             isAll($predicate), isAny($predicate).
 * - operation: insert($index, $values), concat($list).
 *              replace($indexes, $values).
 *              remove($indexes), take($indexFrom, $n), where($predicate), except($predicate).
 *              distinct, shuffle, orderBy($comparator).
 *
 * callbacks
 * - $accumulator: mixed, v => mixed
 * - $comparator : v1,v2 => -1,0,1
 * - $equal     : v1,v2 => bool
 * - $predicate : v => bool
 * - $selector  : v => mixed
 *
 * @see https://msdn.microsoft.com/en-us/library/9eekhta0(v=vs.110).aspx
 * @see https://msdn.microsoft.com/en-us/library/system.collections.arraylist(v=vs.110).aspx
 * @package Saki\Util
 */
class ArrayList implements \IteratorAggregate, \Countable, \ArrayAccess {
    //region fields
    private $innerArray;
    private $writable;

    function __construct(array $innerArray = null, bool $writable = true) {
        $this->writable = true;
        $this->fromArray($innerArray ?? []);
        $this->writable = $writable;
    }

    /**
     * WARNING: should be override if constructor is override.
     * @return static
     */
    function getCopy() {
        return new static($this->innerArray, $this->writable);
    }

    function __toString() {
        return implode(',', $this->innerArray);
    }

    function toArray() {
        return $this->innerArray;
    }

    function toArrayList(callable $selector = null) {
        $a = $selector === null ? $this->innerArray : array_map($selector, $this->innerArray);
        return new ArrayList($a);
    }

    function isWritable() {
        return $this->writable;
    }

    protected function setWritable(bool $writable) {
        $this->writable = $writable;
        return $this;
    }

    protected function assertWritable() {
        if (!$this->isWritable()) {
            throw new \LogicException(
                sprintf('%s[%s] is not writable.', static::class, $this->__toString())
            );
        }
    }
    //endregion

    //region interfaces implementations
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
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayList \$this[$this].");
        }
    }

    function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            $this->innerArray[$offset] = $value;
            return $this;
        } else {
            throw new \InvalidArgumentException("Invalid \$offset[$offset] for ArrayList \$this[$this].");
        }
    }

    function offsetUnset($offset) {
        // not supported since index-rearrange by unset($k1, $k2) may be confused
        throw new \BadMethodCallException();
    }

    function count() {
        return count($this->innerArray);
    }
    //endregion

    //region index properties and getters
    /**
     * @param int|int[] $indexOrIndexes non-empty, unique indexes.
     * @return bool whether indexes exist or not. For empty indexes, return true.
     */
    function indexExist($indexOrIndexes) {
        $indexes = $this->util_boxing($indexOrIndexes);

        $valid = count($indexes) > 0 && (array_unique($indexes) == $indexes);
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $indexOrIndexes[%s] for %s($indexOrIndexes).', implode($indexes), __FUNCTION__)
            );
        }

        return min($indexes) >= 0 && max($indexes) < $this->count();
    }

    /**
     * @param int|int[] $indexOrIndexes non-empty, unique indexes.
     * @return mixed|array values at indexes.
     */
    function getValueAt($indexOrIndexes) {
        $indexes = $this->util_boxing($indexOrIndexes);

        if (!$this->indexExist($indexes)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $indexOrIndexes[%s] for %s($indexOrIndexes).', implode($indexes), __FUNCTION__)
            );
        }

        $values = array_map(function ($i) {
            return $this[$i];
        }, $indexes);
        return $this->util_unboxing($values, is_array($indexOrIndexes));
    }

    /**
     * @return mixed
     */
    function getFirst() {
        return $this->getValueAt(0); // validate
    }

    /**
     * @param int $n
     * @return array
     */
    function getFirstMany(int $n) {
        return $this->getValueAt(range(0, $n - 1)); // validate
    }

    /**
     * @return mixed
     */
    function getLast() {
        return $this->getValueAt($this->count() - 1); // validate
    }

    /**
     * @param int $n
     * @return array
     */
    function getLastMany(int $n) {
        $count = $this->count();
        return $this->getValueAt(range($count - 1, $count - $n)); // validate
    }
    //endregion

    //region value properties and getters
    /**
     * @param mixed|array $valueOrValues non-empty values.
     * @param callable $equal
     * @return bool whether values exist or not. For duplicated values, return true unless duplicated-count ones exist.
     */
    function valueExist($valueOrValues, callable $equal = null) {
        try {
            $this->getIndex($valueOrValues, $equal);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed|array $valueOrValues non-empty values. For duplicated values, duplicated-count unique indexes of them should exist.
     * @param callable $equal
     * @return int|int[] indexes of values. For duplicated values, duplicated-count unique indexes of them are returned.
     */
    function getIndex($valueOrValues, callable $equal = null) {
        $targetValues = $this->util_boxing($valueOrValues);

        $foundIndexes = [];
        $remainValues = $this->innerArray;
        foreach ($targetValues as $v) {
            if ($equal === null) {
                $i = array_search($v, $remainValues);
            } else {
                $i = false;
                foreach ($remainValues as $k => $remainValue) {
                    if ($equal($remainValue, $v)) {
                        $i = $k;
                        break;
                    }
                }
            }

            if ($i === false) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid $value[%s] in $valueOrValues[%s] for %s($indexOrIndexes).',
                        $v, implode($targetValues), __FUNCTION__)
                );
            }

            $foundIndexes[] = $i;
            unset($remainValues[$i]);
        }
        return $this->util_unboxing($foundIndexes, is_array($valueOrValues));
    }

    /**
     * @param callable $predicate
     * @param $default
     */
    function getSingleOrDefault(callable $predicate, $default) {
        $result = null;
        foreach ($this->innerArray as $v) {
            if ($predicate($v)) {
                if ($result !== null) {
                    throw new \LogicException();
                }
                $result = $v;
            }
        }

        return $result ?? $default;
    }
    
    /**
     * @return mixed
     */
    function getCyclicNext($originValue, int $offset = 1) {
        $originIndex = $this->getIndex($originValue); // validate
        $targetIndex = Utils::normalizedMod($originIndex + $offset, $this->count());
        return $this->getValueAt($targetIndex);
    }
    //endregion

    //region properties
    /**
     * @param callable $predicate
     * @return bool
     */
    function isAll(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param callable $predicate
     * @return bool
     */
    function isAny(callable $predicate) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v) == true) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    function isEmpty() {
        return $this->count() == 0;
    }

    /**
     * @return bool
     */
    function isUnique() {
        return array_unique($this->innerArray) == $this - $this->innerArray;
    }

    /**
     * @return bool
     */
    function isSame() {
        return count(array_unique($this->innerArray)) == 1;
    }
    //endregion

    //region getters
    /**
     * @param mixed $initial
     * @param callable $accumulator
     * @return mixed
     */
    function getAggregated($initial, callable $accumulator) {
        return array_reduce($this->innerArray, $accumulator, $initial);
    }

    /**
     * @param callable $predicate
     * @return int
     */
    function getCount(callable $predicate) {
        return count(array_filter($this->innerArray, $predicate));
    }

    /**
     * @param callable $keySelector
     * @return int[]
     */
    function getCounts(callable $keySelector) {
        $keys = $this->select($keySelector)->toArray();
        return array_count_values($keys);
    }

    /**
     * @param callable|null $comparator
     * @return mixed
     */
    function getMax(callable $comparator = null) {
        if ($this->count() == 0) {
            throw new \InvalidArgumentException();
        }

        if ($comparator === null) {
            return max($this->innerArray);
        } else {
            $accumulator = function ($carry, $v) use ($comparator) {
                return $comparator($v, $carry) > 0 ? $v : $carry;
            };
            return array_reduce($this->innerArray, $accumulator, $this->innerArray[0]);
        }
    }

    /**
     * @param callable|null $comparator
     * @return mixed
     */
    function getMin(callable $comparator = null) {
        if ($this->count() == 0) {
            throw new \InvalidArgumentException();
        }

        if ($comparator === null) {
            return min($this->innerArray);
        } else {
            $accumulator = function ($carry, $v) use ($comparator) {
                return $comparator($v, $carry) < 0 ? $v : $carry;
            };
            return array_reduce($this->innerArray, $accumulator, $this->innerArray[0]);
        }
    }

    /**
     * @param callable $selector
     * @return int
     */
    function getSum(callable $selector = null) {
        if ($selector === null) {
            return array_sum($this->innerArray);
        } else {
            $accumulator = function ($carry, $item) use ($selector) {
                return $carry + $selector($item);
            };
            return array_reduce($this->innerArray, $accumulator, 0);
        }
    }
    //endregion

    //region operations
    /**
     * @param callable $equal
     * @return $this
     */
    function distinct(callable $equal = null) {
        $this->assertWritable();
        if ($equal !== null) {
            $result = new ArrayList();
            foreach ($this as $v) {
                $duplicate = $result->isAny(Utils::toPredicate($v));
                if (!$duplicate) {
                    $result->insertLast($v);
                }
            }
            $this->innerArray = $result->toArray();
        } else {
            $this->innerArray = array_unique($this->innerArray);
        }
        return $this;
    }

    /**
     * @param array $a
     * @return $this
     */
    function fromArray(array $a) {
        $this->assertWritable();
        $isCommonIndexArray = empty($a) || array_keys($a) === range(0, count($a) - 1);
        if (!$isCommonIndexArray) {
            throw new \InvalidArgumentException(
                sprintf('$innerArray[%s] should be 0-begin-ascending-int-key-array.', implode(',', $a))
            );
        }
        $this->innerArray = $a;
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable|null $selector
     * @return $this
     */
    function fromSelect(ArrayList $list, callable $selector = null) {
        $this->assertWritable();
        $this->innerArray = $selector === null ? $list->innerArray
            : array_map($selector, $list->innerArray);
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable $arraySelector
     * @return $this
     */
    function fromSelectMany(ArrayList $list, callable $arraySelector) {
        $this->assertWritable();
        $arrays = array_map($arraySelector, $list->innerArray);
        $this->innerArray = array_reduce($arrays, function (array $carry, array $v) {
            return array_merge($carry, $v);
        }, []);
        return $this;
    }

    /**
     * @param ArrayList $firstList
     * @param ArrayList $secondList
     * @param callable $resultSelector
     * @return $this
     */
    function fromZipped(ArrayList $firstList, ArrayList $secondList, callable $resultSelector) {
        $this->assertWritable();
        $a = [];
        foreach ($firstList->innerArray as $v1) {
            foreach ($secondList->innerArray as $v2) {
                $a[] = $resultSelector($v1, $v2);
            }
        }
        $this->innerArray = $a;
        return $this;
    }

    /**
     * @param ArrayList $otherList
     * @return $this
     */
    function concat(ArrayList $otherList) {
        $this->assertWritable();
        $this->innerArray = array_merge($this->innerArray, $otherList->innerArray);
        return $this;
    }

    /**
     * @param $valueOrValues
     * @param int $pos
     * @return $this
     */
    function insert($valueOrValues, int $pos) {
        $this->assertWritable();

        if (!Utils::inRange($pos, 0, $this->count())) {
            throw new \InvalidArgumentException();
        }

        $values = $this->util_boxing($valueOrValues);
        array_splice($this->innerArray, $pos, 0, $values); // array_splice() will rearrange integer-keys
        return $this;
    }

    /**
     * @param $valueOrValues
     * @return $this
     */
    function insertFirst($valueOrValues) {
        return $this->insert($valueOrValues, 0);
    }

    /**
     * @param $valueOrValues
     * @return $this
     */
    function insertLast($valueOrValues) {
        return $this->insert($valueOrValues, $this->count());
    }

    /**
     * @param int|int[] $indexOrIndexes
     * @return $this
     */
    function removeAt($indexOrIndexes) {
        $this->assertWritable();

        if (!$this->indexExist($indexOrIndexes)) {
            throw new \InvalidArgumentException();
        }

        $keyBlacklist = $this->util_boxing($indexOrIndexes);
        $filter = function ($k) use ($keyBlacklist) {
            return !in_array($k, $keyBlacklist);
        };
        $this->innerArray = array_values(array_filter($this->innerArray, $filter, ARRAY_FILTER_USE_KEY));

        return $this;
    }

    /**
     * @param mixed|array $valueOrValues
     * @param callable|null $equal
     * @return $this
     */
    function remove($valueOrValues, callable $equal = null) {
        return $this->removeAt($this->getIndex($valueOrValues, $equal)); // validate
    }

    /**
     * @param int $n
     * @return $this
     */
    function removeFirst(int $n = 1) {
        return $this->removeAt(range(0, $n - 1)); // validate
    }

    /**
     * @param int $n
     * @return $this
     */
    function removeLast(int $n = 1) {
        $last = $this->count() - 1;
        return $this->removeAt(range($last, $last - $n + 1)); // validate
    }

    /**
     * @return $this
     */
    function removeAll() {
        $this->assertWritable();
        $this->innerArray = [];
        return $this;
    }

    /**
     * @param int|int[] $indexOrIndexes
     * @param mixed|array $newValueOrValues
     * @return $this
     */
    function replaceAt($indexOrIndexes, $newValueOrValues) {
        $this->assertWritable();

        list($indexes, $newValues) = [$this->util_boxing($indexOrIndexes), $this->util_boxing($newValueOrValues)];
        $valid = $this->indexExist($indexOrIndexes) && count($indexes) == count($newValues);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $replace = array_combine($indexes, $newValues);
        $this->innerArray = array_replace($this->innerArray, $replace);
        return $this;
    }

    /**
     * @param mixed|array $oldValueOrValues
     * @param mixed|array $newValueOrValues
     * @param callable|null $equal
     * @return $this
     */
    function replace($oldValueOrValues, $newValueOrValues, callable $equal = null) {
        $this->assertWritable();
        $indexOrIndexes = $this->getIndex($oldValueOrValues, $equal); // validate
        return $this->replaceAt($indexOrIndexes, $newValueOrValues);
    }

    /**
     * @param callable $selector
     * @return $this
     */
    function select(callable $selector) {
        $this->assertWritable();
        $this->innerArray = array_map($selector, $this->innerArray);
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    function shiftCyclicLeft(int $n) {
        $this->assertWritable();

        $leftShiftCount = Utils::normalizedMod($n, $this->count());
        if ($leftShiftCount == 0) {
            return $this;
        }

        $newRight = array_slice($this->innerArray, 0, $leftShiftCount);
        $newLeft = array_slice($this->innerArray, $leftShiftCount);
        $newAll = array_merge($newLeft, $newRight);
        $this->innerArray = $newAll;

        return $this;
    }

    /**
     * @return $this
     */
    function shuffle() {
        $this->assertWritable();
        shuffle($this->innerArray);
        return $this;
    }

    /**
     * @param int $indexFrom
     * @param int|null $n
     * @return $this
     */
    function take(int $indexFrom, int $n = null) {
        $takeCount = $n ?? $this->count() - $indexFrom;
        $indexTo = $indexFrom + $takeCount - 1;
        if (!$this->indexExist([$indexFrom, $indexTo])) {
            throw new \InvalidArgumentException();
        }
        $this->innerArray = array_slice($this->innerArray, $indexFrom, $takeCount);
        return $this;
    }

    /**
     * @param callable $predicate
     * @return $this
     */
    function where(callable $predicate) {
        $this->assertWritable();
        $this->innerArray = array_values(array_filter($this->innerArray, $predicate));
        return $this;
    }

    /**
     * @param callable|null $comparator
     * @return $this For empty $this, nothing happen.
     */
    function orderByAscending(callable $comparator = null) {
        $this->assertWritable();
        if ($comparator === null) {
            sort($this->innerArray);
        } else {
            usort($this->innerArray, $comparator);
        }
        return $this;
    }
    //endregion

    //region util
    /**
     * @param mixed|array $valueOrValues
     * @return array
     */
    protected function util_boxing($valueOrValues) {
        return is_array($valueOrValues) ? array_values($valueOrValues) : [$valueOrValues];
    }

    /**
     * For $originIsArray case, unpack 1-element-array to single element.
     * Otherwise keep.
     * @param array $values
     * @param bool $originIsArray
     * @return mixed|array
     */
    protected function util_unboxing(array $values, bool $originIsArray) {
        $valid = $originIsArray || count($values) == 1;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $originIsArray ? $values : $values[0];
    }
    //endregion
}
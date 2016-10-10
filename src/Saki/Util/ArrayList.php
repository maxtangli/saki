<?php
namespace Saki\Util;

/**
 * Common index array in C# ArrayList style.
 *
 * WARNING
 * - size >= 100 cases are NOT optimized.
 * - array values are NOT supported.
 * - null values are NOT supported.
 *
 * features
 * - support foreach/count/isset/get/set.
 * - support object-style and clean array operations.
 * - support readonly
 *
 * callbacks (NOTE: seems much more faster to use getKEY for comparator and equal)
 * - $accumulator: mixed, v => mixed
 * - $comparator: v1,v2 => -1,0,1
 * - $equal: v1,v2 => bool
 * - $predicate: v => bool
 * - $selector: v => mixed
 *
 * @see https://msdn.microsoft.com/en-us/library/9eekhta0(v=vs.110).aspx C# IEnumerable<T> Interface
 * @see https://msdn.microsoft.com/en-us/library/system.collections.arraylist(v=vs.110).aspx C# ArrayList<T> class
 * @see https://msdn.microsoft.com/en-us/library/ms132474(v=vs.110).aspx C# ReadOnlyCollection<T> Class
 * @package Saki\Util
 */
class ArrayList implements \IteratorAggregate, \Countable, \ArrayAccess {
    private $innerArray;
    private $readonly;

    //region construct and convert.
    function __construct(array $a = null) {
        $this->innerArray = array_values($a ?? []);
        $this->readonly = $this->isReadonlyClass();
    }

    /**
     * Get a copy of current instance, where lock status is set to default.
     * WARNING: should be override if constructor is override.
     * @return static
     */
    function getCopy() {
        return new static($this->innerArray);
    }

    /**
     * @return string
     */
    function __toString() {
        return implode(',', $this->innerArray);
    }

    /**
     * @param string $glue
     * @return string
     */
    function toFormatString(string $glue) {
        return implode($glue, $this->innerArray);
    }

    /**
     * @param callable|null $selector
     * @return array
     */
    function toArray(callable $selector = null) {
        return $selector ? array_map($selector, $this->innerArray) : $this->innerArray;
    }

    /**
     * Used in: sub class of ArrayList.
     * @param callable|null $selector
     * @return ArrayList
     */
    function toArrayList(callable $selector = null) {
        return new ArrayList($this->toArray($selector));
    }
    //endregion

    //region readonly
    /**
     * override by trait ReadonlyArrayList
     * @return bool
     */
    protected function isReadonlyClass() {
        return false;
    }

    /**
     * @return bool
     */
    function isReadonly() {
        return $this->readonly;
    }

    /**
     * Assert not readonly. Called before write operations of ArrayList.
     * Note that sub class is not required to call this, since its write operations rely on ArrayList ones.
     */
    protected function assertWritable() {
        if ($this->isReadonly()) {
            throw new \LogicException(
                sprintf('%s[%s] is not writable.', static::class, $this->__toString())
            );
        }
    }

    /**
     * @return $this
     */
    function lock() {
        $this->readonly = true;
        return $this;
    }

    /**
     * @return $this
     */
    function unlock() {
        if ($this->isReadonlyClass()) {
            throw new \BadMethodCallException();
        }
        $this->readonly = false;
        return $this;
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
        return $this->innerArray[$offset];
    }

    function offsetSet($offset, $value) {
        $this->assertInsertPosition($offset);
        $this->innerArray[$offset] = $value;
        return $this;
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
     * @param int[] $indexes
     * @return bool whether indexes exist or not.
     *              For empty indexes, return true.
     *              For duplicated indexes, treat as one single index.
     */
    function indexesExist(array $indexes) {
        return empty($indexes) ||
        (min($indexes) >= 0 && $this->offsetExists(max($indexes)));
    }

    /**
     * @param int[] $indexes
     * @return array
     */
    function getValuesAt(array $indexes) {
        $values = [];
        foreach ($indexes as $i) {
            $values[] = $this->innerArray[$i]; // validate
        }
        return $values;
    }

    /**
     * @return mixed
     */
    function getFirst() {
        return $this->innerArray[0]; // validate
    }

    /**
     * @param callable $predicate
     * @param $default
     * @return mixed
     */
    function getFirstOrDefault(callable $predicate, $default) {
        foreach ($this->innerArray as $v) {
            if ($predicate($v)) {
                return $v;
            }
        }
        return $default;
    }

    /**
     * @param int $n
     * @return array
     */
    function getFirstMany(int $n) {
        return $this->getValuesAt(range(0, $n - 1)); // validate
    }

    /**
     * @return mixed
     */
    function getLast() {
        return $this->innerArray[$this->count() - 1]; // validate
    }

    /**
     * @param int $n
     * @return array
     */
    function getLastMany(int $n) {
        $count = $this->count();
        return $this->getValuesAt(range($count - 1, $count - $n)); // validate
    }
    //endregion

    //region value properties and getters
    /**
     * @param mixed|array $valueOrValues
     * @param callable $equal
     * @return bool whether values exist or not.
     *              For duplicated values, return true unless duplicated-count ones exist.
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
     * @param mixed|array $valueOrValues
     * @param callable $equal
     * @return int|int[] indexes of values.
     *                   For duplicated values, multiple unique indexes are returned.
     */
    function getIndex($valueOrValues, callable $equal = null) {
        $targetValues = $this->util_boxing($valueOrValues);
        $foundIndexes = [];

        foreach ($this->innerArray as $index => $v) {
            $foundTargetKey = false;
            if ($equal === null) {
                $foundTargetKey = array_search($v, $targetValues);
            } else {
                foreach ($targetValues as $targetKey => $targetValue) {
                    if ($equal($v, $targetValue)) {
                        $foundTargetKey = $targetKey;
                        break;
                    }
                }
            }

            if ($foundTargetKey !== false) {
                $foundIndexes[$foundTargetKey] = $index;
                unset($targetValues[$foundTargetKey]);
            }
        }

        if (!empty($targetValues)) {
            throw new \InvalidArgumentException(
                sprintf('Failed to find $targetValues[%s]', implode($targetValues))
            );
        }

        ksort($foundIndexes);

        return $this->util_unboxing($foundIndexes, is_array($valueOrValues));
    }

    /**
     * @param callable|null $predicate
     * @return mixed
     */
    function getSingle(callable $predicate = null) {
        if ($predicate === null) {
            $this->assertSingle();
            return $this->innerArray[0];
        }

        $result = $this->getSingleOrDefault($predicate, null);
        if ($result === null) {
            throw new \BadMethodCallException(
                sprintf('Bad method call of getSingle($predicate) on [%s], 0 matches.', $this)
            );
        }
        return $result;
    }

    /**
     * @param callable $predicate
     * @param $default
     * @return mixed
     */
    function getSingleOrDefault(callable $predicate, $default) {
        $result = null;
        foreach ($this->innerArray as $v) {
            if ($predicate($v)) {
                if ($result !== null) {
                    throw new \BadMethodCallException(
                        sprintf('Bad method call of getSingle($predicate) on [%s], 2 or more matches.', $this)
                    );
                }
                $result = $v;
            }
        }
        return $result ?? $default;
    }
    //endregion

    //region properties
    /**
     * @param callable $predicate
     * @return bool
     */
    function all(callable $predicate) {
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
    function any(callable $predicate) {
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
        return empty($this->innerArray);
    }

    /**
     * @return bool
     */
    function isUnique() {
        return count(array_unique($this->innerArray)) == count($this->innerArray);
    }

    /**
     * @return bool
     */
    function isSame() {
        return count(array_unique($this->innerArray)) == 1;
    }

    /**
     * @param ArrayList $other
     * @param callable $equal
     * @return bool
     */
    function isSequenceEqual(ArrayList $other, callable $equal = null) {
        if ($equal === null) {
            return $this->toArray() == $other->toArray();
        }

        if (!$this->isSequenceSameCount($other)) {
            return false;
        }

        foreach ($this->innerArray as $i => $v1) {
            $v2 = $other[$i];
            if (!$equal($v1, $v2)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ArrayList $other
     * @return bool
     */
    function isSequenceSameCount(ArrayList $other) {
        return $this->count() == $other->count();
    }

    //endregion

    //region getters
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
            $max = $this->innerArray[0];
            foreach ($this->innerArray as $v) {
                $max = $comparator($v, $max) > 0 ? $v : $max;
            }
            return $max;
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
            $min = $this->innerArray[0];
            foreach ($this->innerArray as $v) {
                $min = $comparator($v, $min) < 0 ? $v : $min;
            }
            return $min;
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
            $sum = 0;
            foreach ($this->innerArray as $v) {
                $sum += $selector($v);
            }
            return $sum;
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
            foreach ($this->innerArray as $v) {
                $duplicate = $result->any(Utils::toPredicate($v, $equal));
                if (!$duplicate) {
                    $result->insertLast($v);
                }
            }
            $this->innerArray = $result->toArray();
        } else {
            $this->innerArray = array_values(array_unique($this->innerArray));
        }
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable|null $selector
     * @return $this
     */
    function fromSelect(ArrayList $list, callable $selector = null) {
        $this->assertWritable();
        $this->innerArray = $selector
            ? array_map($selector, $list->innerArray)
            : $list->innerArray;
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable $arrayOrArrayListSelector
     * @return $this
     */
    function fromSelectMany(ArrayList $list, callable $arrayOrArrayListSelector) {
        $this->assertWritable();
        $arrayOrArrayLists = array_map($arrayOrArrayListSelector, $list->innerArray);

        $result = [];
        foreach ($arrayOrArrayLists as $v) {
            $vArray = is_array($v) ? $v : $v->toArray();
            $result = array_merge($result, $vArray);
        }
        $this->innerArray = $result;
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable $keySelector
     * @param callable $groupFilter
     * @return $this
     */
    function fromGroupBy(ArrayList $list, callable $keySelector, callable $groupFilter = null) {
        $m = [];
        $register = function (array &$m, $k, $v) {
            $m[$k] = $m[$k] ?? new ArrayList();
            $m[$k]->insertLast($v);
        };
        foreach ($list as $v) {
            $k = $keySelector($v);
            $register($m, $k, $v);
        }

        $this->innerArray = array_values($m);
        if ($groupFilter !== null) {
            $this->where($groupFilter);
        }
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable $resultSelector
     * @return $this
     */
    function fromCombination(ArrayList $list, callable $resultSelector) {
        $this->assertWritable();
        $a = [];
        foreach ($list->innerArray as $k1 => $v1) {
            foreach ($list->innerArray as $k2 => $v2) {
                if ($k1 != $k2) {
                    $a[] = $resultSelector($v1, $v2);
                }
            }
        }
        $this->innerArray = $a;
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
        $this->assertInsertPosition($pos);

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
     * @param $value
     * @param int $newCount
     * @return $this
     */
    function fillToCount($value, int $newCount) {
        $count = $this->count();
        $nTodo = max(0, $newCount - $count);
        $values = array_fill($count, $nTodo, $value);
        return $this->insertLast($values);
    }

    /**
     * @param int|int[] $indexOrIndexes
     * @return $this
     */
    function removeAt($indexOrIndexes) {
        $this->assertWritable();
        $indexes = $this->util_boxing($indexOrIndexes);
        if (!$this->indexesExist($indexes)) {
            throw new \InvalidArgumentException();
        }

        $result = [];
        foreach ($this->innerArray as $k => $v) {
            if (!in_array($k, $indexes)) {
                $result[] = $v;
            }
        }
        $this->innerArray = $result;
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
        $valid = $this->indexesExist($indexes) && count($indexes) == count($newValues);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $replace = array_combine($indexes, $newValues);
        $this->innerArray = array_replace($this->innerArray, $replace);
        return $this;
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param callable|null $equal
     * @return $this
     */
    function replace($oldValue, $newValue, callable $equal = null) {
        $this->assertWritable();
        $index = $this->getIndex($oldValue, $equal); // validate
        return $this->replaceAt($index, $newValue);
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
     * @param callable $callable
     * @return $this
     */
    function walk(callable $callable) {
        $this->assertWritable();
        array_walk($this->innerArray, $callable);
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
     * @throws \InvalidArgumentException if failed to shrink $this->count() to $n.
     */
    function take(int $indexFrom, int $n = null) {
        $this->assertWritable();

        $takeCount = $n ?? $this->count() - $indexFrom;
        $result = array_slice($this->innerArray, $indexFrom, $takeCount);
        if (count($result) != $takeCount) {
            throw new \InvalidArgumentException(
                sprintf('Failed to take from $indexFrom[%s] by $takeCount[%s], count($result)[%s].',
                    $indexFrom, $takeCount, count($result))
            );
        }

        $this->innerArray = $result;
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

    /**
     * @param callable|null $comparator
     * @return $this For empty $this, nothing happen.
     */
    function orderByDescending(callable $comparator = null) {
        $this->assertWritable();
        if ($comparator === null) {
            rsort($this->innerArray);
        } else {
            usort($this->innerArray, $this->util_revertComparator($comparator));
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
        return is_array($valueOrValues) ? $valueOrValues : [$valueOrValues];
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

    /**
     * @param callable $comparator
     * @return \Closure
     */
    protected function util_revertComparator(callable $comparator) {
        return function ($v1, $v2) use ($comparator) {
            return -$comparator($v1, $v2);
        };
    }

    /**
     * @param int $n
     */
    protected function assertGetPosition(int $n) {
        if (!Utils::inRange($n, 0, $this->count() - 1)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid get position $n[%s].', $n)
            );
        }
    }

    /**
     * @param int $n
     */
    protected function assertInsertPosition(int $n) {
        if (!Utils::inRange($n, 0, $this->count())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid insert position $n[%s].', $n)
            );
        }
    }

    protected function assertSingle() {
        if ($this->count() != 1) {
            throw new \InvalidArgumentException(
                'Failed to assert $this->count() == 1.'
            );
        }
    }
    //endregion
}

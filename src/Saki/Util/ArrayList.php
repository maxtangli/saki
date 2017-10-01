<?php
namespace Saki\Util;

/**
 * C# ArrayList style index array.
 *
 * WARNING
 * - size > 100 cases are NOT optimized.
 * - array values are NOT supported.
 * - null values are NOT supported.
 *
 * features
 * - support foreach/count/isset/get/set.
 * - support object-style and clean array operations.
 * - support readonly
 *
 * callbacks
 * - $predicate: v => bool
 * - $selector: v => mixed
 *
 * @see https://msdn.microsoft.com/en-us/library/9eekhta0(v=vs.110).aspx C# IEnumerable<T> Interface
 * @see https://msdn.microsoft.com/en-us/library/system.collections.arraylist(v=vs.110).aspx C# ArrayList<T> class
 * @see https://msdn.microsoft.com/en-us/library/ms132474(v=vs.110).aspx C# ReadOnlyCollection<T> Class
 * @see https://github.com/illuminate/support/blob/master/Collection.php illuminate/support/Collection.php
 * @package Saki\Util
 */
class ArrayList implements \IteratorAggregate, \Countable, \ArrayAccess {
    //region construct and convert
    private $a;

    function __construct(array $a = null) {
        $this->a = array_values($a ?? []);
    }

    /**
     * Get a copy of current instance, where lock status is set to default.
     * WARNING: should be override if constructor is override.
     * @return static
     */
    function getCopy() {
        return new static($this->a);
    }

    /**
     * @return string
     */
    function __toString() {
        return implode(',', $this->a);
    }

    /**
     * @param string $glue
     * @return string
     */
    function toFormatString(string $glue) {
        return implode($glue, $this->a);
    }

    /**
     * @param callable|null $selector
     * @return array
     */
    function toArray(callable $selector = null) {
        return is_null($selector) ? $this->a : array_map($selector, $this->a);
    }

    /**
     * @param $value
     * @return array
     */
    function toRepeatArray($value) {
        return array_pad([], $this->count(), $value);
    }

    /**
     * @param int $size
     * @return array
     */
    function toChunks(int $size) {
        return array_chunk($this->toArray(), $size);
    }

    /**
     * @param callable $keySelector
     * @param callable $selector
     * @return array
     */
    function toMap(callable $keySelector, callable $selector) {
        $keys = array_map($keySelector, $this->a);
        $values = $this->toArray($selector);
        return array_combine($keys, $values);
    }

    /**
     * @param callable $keySelector
     * @param callable $groupFilter
     * @return array
     */
    function toGroups(callable $keySelector, callable $groupFilter = null) {
        $register = function (array &$m, $k, $v) {
            $m[$k] = $m[$k] ?? new static();
            $m[$k]->insertLast($v);
        };
        $map = [];
        foreach ($this->a as $v) {
            $k = $keySelector($v);
            $register($map, $k, $v);
        }

        if (isset($groupFilter)) {
            $map = array_filter($map);
        }
        return $map;
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
    protected function assertWritable() {
        if (isset($this->readonly) && $this->readonly) {
            throw new \LogicException(
                sprintf('%s[%s] is not writable.', static::class, $this->__toString())
            );
        }
    }
    //endregion

    //region interfaces implementations
    function getIterator() {
        return new \ArrayIterator($this->a);
    }

    function offsetExists($offset) {
        return isset($this->a[$offset]);
    }

    function offsetGet($offset) {
        $this->assertGetIndex($offset);
        return $this->a[$offset];
    }

    function offsetSet($offset, $value) {
        $i = $offset ?? $this->count();
        $this->assertInsertIndex($i);
        $this->a[$i] = $value;
        return $this;
    }

    function offsetUnset($offset) {
        // not supported since index-rearrange by unset($k1, $k2) may be confused
        throw new \BadMethodCallException();
    }

    function count() {
        return count($this->a);
    }
    //endregion

    //region index properties and getters
    /**
     * @return int
     */
    function getFirstIndex() {
        $this->assertNotEmpty();
        return 0;
    }

    /**
     * @return int
     */
    function getLastIndex() {
        $this->assertNotEmpty();
        return $this->count() - 1;
    }

    /**
     * @param int[] $indexes
     * @return bool whether indexes exist or not.
     *              For empty indexes, return true.
     *              For duplicated indexes, treat as one single index.
     */
    function indexesExist(array $indexes) {
        return empty($indexes) ||
            (min($indexes) >= 0 && isset($this->a[max($indexes)]));
    }

    /**
     * @param int[] $indexes
     * @return array
     */
    function getValuesAt(array $indexes) {
        return array_map([$this, 'offsetGet'], $indexes); // validate
    }

    /**
     * @return mixed
     */
    function getFirst() {
        return $this->offsetGet(0); // validate
    }

    /**
     * @param callable $predicate
     * @param $default
     * @return mixed
     */
    function getFirstOrDefault(callable $predicate = null, $default = null) {
        if (is_null($predicate)) {
            return $this->isNotEmpty() ? $this->getFirst() : $default;
        }

        foreach ($this->a as $v) {
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
        return $this->offsetGet($this->count() - 1); // validate
    }

    /**
     * @param int $n
     * @return array
     */
    function getLastMany(int $n) {
        $count = $this->count();
        return $this->getValuesAt(range($count - 1, $count - $n)); // validate
    }

    /**
     * @return mixed
     */
    function getRandom() {
        $this->assertNotEmpty();
        $index = mt_rand(0, $this->count() - 1);
        return $this->offsetGet($index);
    }
    //endregion

    //region value properties and getters
    /**
     * @param mixed|array $valueOrValues
     * @param callable $selector
     * @return bool whether values exist or not.
     *              For duplicated values, return true unless duplicated-count ones exist.
     */
    function valueExist($valueOrValues, callable $selector = null) {
        try {
            $this->getIndex($valueOrValues, $selector);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed|array $valueOrValues
     * @param callable $selector
     * @return int|int[] indexes of values.
     *                   For duplicated values, multiple unique indexes are returned.
     */
    function getIndex($valueOrValues, callable $selector = null) {
        $values = $this->util_boxing($valueOrValues);

        $foundIndexes = [];
        $base = is_null($selector) ? $this->a : array_map($selector, $this->a);
        $targets = is_null($selector) ? $values : array_map($selector, $values);
        foreach ($targets as $target) {
            $index = array_search($target, $base);
            if ($index === false) break;

            $foundIndexes[] = $index;
            unset($base[$index]);
        }

        $allFound = count($foundIndexes) == count($targets);
        if (!$allFound) {
            throw new \InvalidArgumentException(
                sprintf('Failed to find $values[%s] by $targets[%s]'
                    , implode(',', $values), implode(',', $targets))
            );
        }

        return $this->util_unboxing($foundIndexes, is_array($valueOrValues));
    }

    /**
     * @param callable|null $predicate
     * @return mixed
     */
    function getSingle(callable $predicate = null) {
        if (is_null($predicate)) {
            $this->assertSingle();
            return $this->a[0];
        }

        $result = $this->getSingleOrDefault($predicate, null);
        if (is_null($result)) {
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
        foreach ($this->a as $v) {
            if ($predicate($v)) {
                if (isset($result)) {
                    throw new \BadMethodCallException(
                        sprintf('Bad method call of getSingle($predicate) on [%s], 2 or more matches.', $this)
                    );
                }
                $result = $v;
            }
        }
        return $result ?? $default;
    }

    /**
     * @param callable $predicate
     * @param callable $generator
     * @return mixed
     */
    function getSingleOrGenerate(callable $predicate, callable $generator) {
        return $this->getSingleOrDefault($predicate, null) ?? call_user_func($generator);
    }
    //endregion

    //region properties
    /**
     * @param callable $predicate
     * @return bool
     */
    function all(callable $predicate) {
        foreach ($this->a as $v) {
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
        foreach ($this->a as $v) {
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
        return empty($this->a);
    }

    /**
     * @return bool
     */
    function isNotEmpty() {
        return !empty($this->a);
    }

    /**
     * @return bool
     */
    function isUnique() {
        return count(array_unique($this->a)) == count($this->a);
    }

    /**
     * @return bool
     */
    function isSame() {
        return count(array_unique($this->a)) == 1;
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
        return count(array_filter($this->a, $predicate));
    }

    /**
     * @param callable $selector
     * @param int $minCount
     * @return \int[]
     */
    function getCounts(callable $selector, int $minCount = 0) {
        $keys = array_map($selector, $this->a);
        $counts = array_count_values($keys);
        if ($minCount > 0) {
            $match = function ($n) use ($minCount) {
                return $n >= $minCount;
            };
            $counts = array_filter($counts, $match);
        }
        return $counts;
    }

    /**
     * @param callable|null $selector
     * @return mixed
     * @throws \InvalidArgumentException If empty.
     */
    function getMin(callable $selector = null) {
        $this->assertNotEmpty();

        if (is_null($selector)) {
            return min($this->a);
        }

        $keys = array_map($selector, $this->a);
        $i = array_search(min($keys), $keys);
        return $this->a[$i];
    }

    /**
     * @param callable|null $selector
     * @return mixed
     * @throws \InvalidArgumentException If empty.
     */
    function getMax(callable $selector = null) {
        $this->assertNotEmpty();

        if (is_null($selector)) {
            return max($this->a);
        }

        $keys = array_map($selector, $this->a);
        $i = array_search(max($keys), $keys);
        return $this->a[$i];
    }

    /**
     * @param callable $selector
     * @return int
     */
    function getSum(callable $selector = null) {
        $values = is_null($selector) ? $this->a : array_map($selector, $this->a);
        return array_sum($values);
    }
    //endregion

    //region operations
    /**
     * Two elements are considered equal if and only if (string) $elem1 === (string) $elem2.
     * @return $this
     */
    function distinct() {
        $this->assertWritable();
        $this->a = array_values(array_unique($this->a));
        return $this;
    }

    /**
     * @param int $count
     * @param callable $generator
     * @return $this
     */
    function fromGenerator(int $count, callable $generator) {
        $a = [];
        $nTodo = $count;
        while ($nTodo-- > 0) {
            $a[] = $generator();
        }
        $this->a = $a;
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable|null $selector
     * @return $this
     */
    function fromSelect(ArrayList $list, callable $selector = null) {
        $this->assertWritable();
        $this->a = is_null($selector) ? $list->a : array_map($selector, $list->a);
        return $this;
    }

    /**
     * @param ArrayList $list
     * @param callable $arrayOrArrayListSelector
     * @return $this
     */
    function fromSelectMany(ArrayList $list, callable $arrayOrArrayListSelector) {
        $this->assertWritable();
        $arrayOrArrayLists = array_map($arrayOrArrayListSelector, $list->a);
        $merge = function (array $result, $v) {
            $vArray = is_array($v) ? $v : $v->toArray();
            return array_merge($result, $vArray);
        };
        $this->a = array_reduce($arrayOrArrayLists, $merge, []);
        return $this;
    }

    /**
     * @param ArrayList $firstList
     * @param ArrayList $secondList
     * @param callable $resultSelector
     * @return $this
     */
    function fromMapping(ArrayList $firstList, ArrayList $secondList, callable $resultSelector) {
        $this->assertWritable();
        if ($firstList->count() != $secondList->count()) {
            throw new \InvalidArgumentException();
        }

        $a = [];
        foreach ($firstList->a as $k => $v1) {
            $v2 = $secondList[$k];
            $a[] = $resultSelector($v1, $v2);
        }
        $this->a = $a;
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
        foreach ($firstList->a as $v1) {
            foreach ($secondList->a as $v2) {
                $a[] = $resultSelector($v1, $v2);
            }
        }
        $this->a = $a;
        return $this;
    }

    /**
     * @param ArrayList $otherList
     * @return $this
     */
    function concat(ArrayList $otherList) {
        $this->assertWritable();
        $this->a = array_merge($this->a, $otherList->a);
        return $this;
    }

    /**
     * @param $valueOrValues
     * @param int $pos
     * @return $this
     */
    function insert($valueOrValues, int $pos) {
        $this->assertWritable();
        $this->assertInsertIndex($pos);

        $values = $this->util_boxing($valueOrValues);
        array_splice($this->a, $pos, 0, $values); // array_splice() will rearrange integer-keys
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
        $this->assertGetIndex($indexOrIndexes);

        $indexes = $this->util_boxing($indexOrIndexes);
        foreach ($indexes as $index) {
            unset($this->a[$index]);
        }
        $this->a = array_values($this->a);

        return $this;
    }

    /**
     * @param mixed|array $valueOrValues
     * @param callable|null $selector
     * @return $this
     */
    function remove($valueOrValues, callable $selector = null) {
        return $this->removeAt($this->getIndex($valueOrValues, $selector)); // validate
    }

    /**
     * @param int $n
     * @return $this
     */
    function removeFirst(int $n = 1) {
        return $this->removeAt(range(0, $n - 1)); // validate
    }

    /**
     * @return mixed
     */
    function shift() {
        $first = $this->getFirst(); // validate
        $this->removeFirst();
        return $first;
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
     * @return mixed
     */
    function pop() {
        $last = $this->getLast(); // validate
        $this->removeLast();
        return $last;
    }

    /**
     * @return $this
     */
    function removeAll() {
        $this->assertWritable();
        $this->a = [];
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
        $this->a = array_replace($this->a, $replace);
        return $this;
    }

    /**
     * @param $value
     * @return ArrayList
     */
    function replaceFirst($value) {
        return $this->replaceAt($this->getFirstIndex(), $value); // validate
    }

    /**
     * @param $value
     * @return ArrayList
     */
    function replaceLast($value) {
        return $this->replaceAt($this->getLastIndex(), $value); // validate
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return $this
     */
    function replace($oldValue, $newValue) {
        $this->assertWritable();
        $index = $this->getIndex($oldValue); // validate
        return $this->replaceAt($index, $newValue);
    }

    /**
     * @param callable $selector
     * @return $this
     */
    function select(callable $selector) {
        $this->assertWritable();
        $this->a = array_map($selector, $this->a);
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    function walk(callable $callable) {
        $this->assertWritable();
        array_walk($this->a, $callable);
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

        $newRight = array_slice($this->a, 0, $leftShiftCount);
        $newLeft = array_slice($this->a, $leftShiftCount);
        $newAll = array_merge($newLeft, $newRight);
        $this->a = $newAll;
        return $this;
    }

    /**
     * @param int $indexFrom
     * @param int $indexTo
     * @return $this
     */
    function move(int $indexFrom, int $indexTo) {
        $this->assertGetIndex([$indexFrom, $indexTo]);
        if ($indexFrom != $indexTo) {
            $v = $this->offsetGet($indexFrom);
            $this->removeAt($indexFrom)->insert($v, $indexTo);
        }
        return $this;
    }

    /**
     * @param int $index1
     * @param int $index2
     * @return $this
     */
    function swap(int $index1, int $index2) {
        $this->assertGetIndex([$index1, $index2]);
        if ($index1 != $index2) {
            $temp = $this->a[$index1];
            $this->a[$index1] = $this->a[$index2];
            $this->a[$index2] = $temp;
        }
        return $this;
    }

    /**
     * @return $this
     */
    function reverse() {
        $this->a = array_reverse($this->a);
        return $this;
    }

    /**
     * @return $this
     */
    function shuffle() {
        $this->assertWritable();
        shuffle($this->a);
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
        $result = array_slice($this->a, $indexFrom, $takeCount);
        if (count($result) != $takeCount) {
            throw new \InvalidArgumentException(
                sprintf('Failed to take from $indexFrom[%s] by $takeCount[%s], count($result)[%s].',
                    $indexFrom, $takeCount, count($result))
            );
        }

        $this->a = $result;
        return $this;
    }

    /**
     * @param callable $predicate
     * @return $this
     */
    function where(callable $predicate) {
        $this->assertWritable();
        $this->a = array_values(array_filter($this->a, $predicate));
        return $this;
    }

    /**
     * @param callable|null $selector
     * @return $this For empty $this, nothing happen.
     */
    function orderByAscending(callable $selector = null) {
        if (is_null($selector)) {
            sort($this->a);
            return $this;
        }

        $sortKeys = array_map($selector, $this->a);
        asort($sortKeys);
        $newIndexes = array_keys($sortKeys);

        $this->a = array_map([$this, 'offsetGet'], $newIndexes);
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
        if ($originIsArray) return $values;

        if (count($values) != 1) {
            throw new \InvalidArgumentException();
        }
        return $values[0];
    }

    /**
     * @param $indexOrIndexes
     * @return $this
     */
    protected function assertGetIndex($indexOrIndexes) {
        $indexes = $this->util_boxing($indexOrIndexes);
        if (!$this->indexesExist($indexes)) {
            throw new \InvalidArgumentException(
                sprintf('ArrayList: Invalid get position $n[%s].'
                    , implode(',', $indexes))
            );
        }
        return $this;
    }

    /**
     * @param int $index
     * @return $this
     */
    protected function assertInsertIndex(int $index) {
        if (!Utils::inRange($index, 0, $this->count())) {
            throw new \InvalidArgumentException(
                "ArrayList: Invalid insert position \$index[$index]."
            );
        }
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    function assertCount(int $n) {
        if ($this->count() != $n) {
            throw new \InvalidArgumentException(
                "ArrayList: Failed to assert \$this->count() == $n."
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function assertSingle() {
        return $this->assertCount(1);
    }

    /**
     * @return $this
     */
    protected function assertNotEmpty() {
        if ($this->isEmpty()) {
            throw new \InvalidArgumentException(
                "ArrayList: Failed to assert not empty."
            );
        }
        return $this;
    }
    //endregion
}

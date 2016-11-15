<?php

namespace ArrayLikeObjectTest;

use Saki\Util\ArrayList;

class ArrayListTest extends \SakiTestCase {
    function testIterate() {
        $a = [0, 1, 2, 3, 4];
        $obj = new ArrayList($a);
        foreach ($obj as $i => $v) {
            $this->assertTrue($obj->offsetExists($i));
            $this->assertSame($v, $obj[$i]);
            $obj[$i] = -1;
        }
        $this->assertEquals(array_fill(0, count($a), -1), $obj->toArray());
        $this->assertEquals(count($a), count($obj));

        $obj[] = -1;
        $this->assertEquals(count($a) + 1, count($obj));
    }

    function testIndex() {
        $a = [0, 1, 2, 3, 4, 0];
        $obj = new ArrayList($a);

        $this->assertTrue($obj->indexesExist([]));
        $this->assertTrue($obj->indexesExist([0]));
        $this->assertTrue($obj->indexesExist([0, 5]));
        $this->assertFalse($obj->indexesExist([0, 6]));

        $this->assertEquals(0, $obj[0]);
        $this->assertEquals([0], $obj->getValuesAt([0]));
    }

    function testValue() {
        $a = [0, 1, 2, 3, 4, 0];
        $obj = new ArrayList($a);

        $this->assertEquals(0, $obj->getIndex(0));
        $this->assertEquals([0], $obj->getIndex([0]));
        $this->assertEquals([0, 5], $obj->getIndex([0, 0]));
        $this->assertEquals([0, 3, 5], $obj->getIndex([0, 3, 0]));

        $this->assertTrue($obj->valueExist([]));
        $this->assertTrue($obj->valueExist(0));
        $this->assertTrue($obj->valueExist([0]));
        $this->assertTrue($obj->valueExist([0, 0]));
        $this->assertFalse($obj->valueExist([0, 0, 0]));
    }

    /**
     * @dataProvider valueExceptionProvider
     * @expectedException \InvalidArgumentException
     */
    function testValueException(array $a, $v) {
        $obj = new ArrayList($a);
        $obj->getIndex($v);
    }

    function valueExceptionProvider() {
        return [
            [[0, 1, 2, 0], 3],
            [[0, 1, 2, 0], [3]],
            [[0, 1, 2, 0], [0, 0, 0]],
        ];
    }

    function testGetMinMax() {
        $a = [0, 1, 2, 3, 4];
        $obj = new ArrayList($a);
        $this->assertEquals(0, $obj->getMin());
        $this->assertEquals(4, $obj->getMax());
    }

    function testToGroup() {
        $source = new ArrayList([1, 2, 2, 3, 3, 3]);
        $actual = $source->toGroups(function ($v) {
            return $v;
        });
        $expected = [
            1 => new ArrayList([1]),
            2 => new ArrayList([2, 2]),
            3 => new ArrayList([3, 3, 3])];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider insertProvider
     */
    function testInsert(array $expected, array $a, $insertValue, $pos) {
        $obj = new ArrayList($a);
        $obj->insert($insertValue, $pos);
        $this->assertEquals($expected, $obj->toArray());
    }

    function insertProvider() {
        return [
            [[], [], [], 0], [[1], [1], [], 0],
            [[0], [], 0, 0],
            [[0], [], [0], 0],
            [[0, 0], [], [0, 0], 0],
            [[1, 0], [0], [1], 0],
            [[0, 1], [0], [1], 1],
        ];
    }

    function testInsertFirst() {
        $a = [-1, 0, 1];
        $obj = new ArrayList($a);
        $obj->insertFirst(-2);
        $this->assertEquals([-2, -1, 0, 1], $obj->toArray());
        $obj->insertFirst([-3]);
        $this->assertEquals([-3, -2, -1, 0, 1], $obj->toArray());
        $obj->insertFirst([-5, -4]);
        $this->assertEquals([-5, -4, -3, -2, -1, 0, 1], $obj->toArray());
    }

    function testInsertLast() {
        $a = [-1, 0, 1];
        $obj = new ArrayList($a);
        $obj->insertLast(2);
        $this->assertEquals([-1, 0, 1, 2], $obj->toArray());
        $obj->insertLast([3]);
        $this->assertEquals([-1, 0, 1, 2, 3], $obj->toArray());
        $obj->insertLast([4, 5]);
        $this->assertEquals([-1, 0, 1, 2, 3, 4, 5], $obj->toArray());
    }

    /**
     * @dataProvider fillToCountProvider
     */
    function testFillToCount($expected, $a, $value, $newCount) {
        $this->assertEquals($expected, (new ArrayList($a))->fillToCount($value, $newCount)->toArray());
    }

    function fillToCountProvider() {
        return [
            [[], [], 0, 0],
            [[0], [], 0, 1],
            [[0, 0], [], 0, 2],

            [[1], [1], 0, 0],
            [[1], [1], 0, 1],
            [[1, 0], [1], 0, 2],
            [[1, 0, 0], [1], 0, 3],
        ];
    }

    function testOrderBy() {
        $this->assertEquals([], (new ArrayList())->orderByAscending()->toArray());
        $this->assertEquals([-2, 1, 3], (new ArrayList([1, 3, -2]))->orderByAscending()->toArray());
    }

    /**
     * @dataProvider replaceByIndexProvider
     */
    function testReplaceByIndex(array $expected, array $a, $index, $value) {
        $obj = new ArrayList($a);
        $obj->replaceAt($index, $value);
        $this->assertEquals($expected, $obj->toArray());
    }

    function replaceByIndexProvider() {
        return [
            [[0, -1, 2], [0, 1, 2], 1, -1],
            [[0, -1, 2], [0, 1, 2], [1], [-1]],
            [[0, -1, -2], [0, 1, 2], [1, 2], [-1, -2]],
        ];
    }

    /**
     * @dataProvider shiftCyclicLeftProvider
     */
    function testShiftCyclicLeft(array $a, $n, $expected) {
        $obj = new ArrayList($a);
        $obj->shiftCyclicLeft($n);
        $this->assertEquals($expected, $obj->toArray());
    }

    function shiftCyclicLeftProvider() {
        return [
            [[1, 2, 3, 4], 1, [2, 3, 4, 1]], [[1, 2, 3, 4], 5, [2, 3, 4, 1]], [[1, 2, 3, 4], -3, [2, 3, 4, 1]],
            [[1, 2, 3, 4], 2, [3, 4, 1, 2]], [[1, 2, 3, 4], 6, [3, 4, 1, 2]], [[1, 2, 3, 4], -2, [3, 4, 1, 2]],
            [[1, 2, 3, 4], 3, [4, 1, 2, 3]], [[1, 2, 3, 4], 7, [4, 1, 2, 3]], [[1, 2, 3, 4], -1, [4, 1, 2, 3]],
            [[1, 2, 3, 4], 0, [1, 2, 3, 4]], [[1, 2, 3, 4], 4, [1, 2, 3, 4]], [[1, 2, 3, 4], 8, [1, 2, 3, 4]], [[1, 2, 3, 4], -4, [1, 2, 3, 4]], [[1, 2, 3, 4], -8, [1, 2, 3, 4]],
            // empty array
            [[], 0, []], [[], 1, []], [[], -1, []],
            // one element array
            [[1], 0, [1]], [[1], 1, [1]], [[1], -1, [1]],
        ];
    }

    /**
     * @dataProvider removeProvider
     */
    function testRemove(array $expectedRemain, array $a, $removeIndex) {
        $obj = new ArrayList($a);
        $obj->removeAt($removeIndex);
        $this->assertEquals($expectedRemain, $obj->toArray());
    }

    function removeProvider() {
        return [
            [[1, 2], [0, 1, 2], 0,],
            [[1, 2], [0, 1, 2], [0]],
            [[1], [0, 1, 2], [0, 2]]
        ];
    }

    function testRemoveFirst() {
        $a = [-5, -4, -3, -2, -1, 0];
        $obj = new ArrayList($a);

        $this->assertEquals(-5, $obj->getFirst());
        $this->assertEquals([-4, -3, -2, -1, 0], $obj->removeFirst()->toArray());

        $this->assertEquals([-4, -3], $obj->getFirstMany(2));
        $this->assertEquals([-2, -1, 0], $obj->removeFirst(2)->toArray());
    }

    function testRemoveLast() {
        $a = [0, 1, 2, 3, 4, 5];
        $obj = new ArrayList($a);

        $this->assertEquals([5], $obj->getLastMany(1));
        $this->assertEquals([0, 1, 2, 3, 4], $obj->removeLast(1)->toArray());

        $this->assertEquals([4, 3], $obj->getLastMany(2));
        $this->assertEquals([0, 1, 2], $obj->removeLast(2)->toArray());
    }

    /**
     * @dataProvider takeProvider
     */
    function testTake($expected, $a, $indexFrom, $n) {
        $this->assertEquals($expected, (new ArrayList($a))->take($indexFrom, $n)->toArray());
    }

    function takeProvider() {
        return [
            [[], [], 0, 0],
            [[0, 1, 2], [0, 1, 2], 0, 3],
            [[1, 2], [0, 1, 2], 1, 2],
            [[2], [0, 1, 2], 2, 1],
            [[], [0, 1, 2], 3, 0],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider takeExceptionProvider
     */
    function testTakeException($a, $indexFrom, $n) {
        (new ArrayList($a))->take($indexFrom, $n);
    }

    function takeExceptionProvider() {
        return [
            [[], 0, 1],
            [[], 0, -1],
            [[0, 1, 2], 0, -1],
            [[0, 1, 2], 0, 4],
            [[0, 1, 2], 1, 3],
            [[0, 1, 2], 2, 2],
            [[0, 1, 2], 3, 1],
        ];
    }
}

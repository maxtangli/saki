<?php

namespace ArrayLikeObjectTest;

use Saki\Util\ArrayLikeObject;

class ArrayLikeObjectTest extends \PHPUnit_Framework_TestCase {
    function testIterate() {
        $a = [0, 1, 2, 3, 4];
        $obj = new ArrayLikeObject($a);
        foreach ($obj as $i => $v) {
            $this->assertTrue($obj->offsetExists($i));
            $this->assertSame($v, $obj[$i]);
            $obj[$i] = -1;
        }
        $this->assertEquals(array_fill(0, count($a), -1), $obj->toArray());
        $this->assertEquals(count($a), count($obj));
    }

    function testRetrieveValue() {
        $a = [0, 1, 2, 3, 4, 0];
        $obj = new ArrayLikeObject($a);

        $this->assertTrue($obj->valueExist(0));
        $this->assertTrue($obj->valueExist([0]));
        $this->assertTrue($obj->valueExist([0, 0]));
        $this->assertFalse($obj->valueExist([0, 0, 0]));

        $this->assertEquals(0, $obj->valueToIndex(0));
        $this->assertEquals([0], $obj->valueToIndex([0]));
        $this->assertEquals([0, 5], $obj->valueToIndex([0, 0]));
        $this->assertEquals([0, 3, 5], $obj->valueToIndex([0, 3, 0]));
    }

    function testRetrieveIndex() {
        $a = [0, 1, 2, 3, 4, 0];
        $obj = new ArrayLikeObject($a);

        $this->assertTrue($obj->indexExist(0));
        $this->assertTrue($obj->indexExist([0]));
        $this->assertTrue($obj->indexExist([0, 0]));
        $this->assertTrue($obj->indexExist([0, 5]));
        $this->assertFalse($obj->indexExist([0, 6]));

        $this->assertEquals(0, $obj->indexToValue(0));
        $this->assertEquals([0], $obj->indexToValue([0]));
        $this->assertEquals([0, 0], $obj->indexToValue([0, 0]));
        $this->assertEquals([0, 3, 0], $obj->indexToValue([0, 3, 0]));
    }

    /**
     * @dataProvider valueToIndexExceptionProvider
     * @expectedException \InvalidArgumentException
     */
    function testValueToIndexException(array $a, $v) {
        $obj = new ArrayLikeObject($a);
        $obj->valueToIndex($v);
    }

    function valueToIndexExceptionProvider() {
        return [
            [[0, 1, 2, 0], 3],
            [[0, 1, 2, 0], [3]],
            [[0, 1, 2, 0], [0, 0, 0]],
        ];
    }

    /**
     * @dataProvider replaceByIndexProvider
     */
    function testReplaceByIndex(array $expected, array $a, $index, $value) {
        $obj = new ArrayLikeObject($a);
        $obj->replaceByIndex($index, $value);
        $this->assertEquals($expected, $obj->toArray());
    }

    function replaceByIndexProvider() {
        return [
            [[0,-1,2], [0,1,2], 1, -1],
            [[0,-1,2], [0,1,2], [1], [-1]],
            [[0,-1,-2], [0,1,2], [1,2], [-1,-2]],
        ];
    }

    /**
     * @dataProvider insertProvider
     */
    function testInsert(array $expected, array $a, $insertValue, $pos) {
        $obj = new ArrayLikeObject($a);
        $obj->insert($insertValue, $pos);
        $this->assertEquals($expected, $obj->toArray());
    }

    function insertProvider() {
        return [
            [[0], [], 0, 0],
            [[0], [], [0], 0],
            [[0, 0], [], [0, 0], 0],
            [[1, 0], [0], [1], 0],
            [[0, 1], [0], [1], 1],
        ];
    }

    /**
     * @dataProvider removeProvider
     */
    function testRemove($expectedReturn, array $expectedRemain, array $a, $removeIndex) {
        $obj = new ArrayLikeObject($a);
        $ret = $obj->removeByIndex($removeIndex);
        $this->assertEquals($expectedReturn, $ret);
        $this->assertEquals($expectedRemain, $obj->toArray());
    }

    function removeProvider() {
        return [
            [0, [1, 2], [0, 1, 2], 0,],
            [[0], [1, 2], [0, 1, 2], [0]],
            [[0, 2], [1], [0, 1, 2], [0, 2]]
        ];
    }

    function testPush() {
        $a = [-1, 0, 1];
        $obj = new ArrayLikeObject($a);
        $obj->push(2);
        $this->assertEquals([-1, 0, 1, 2], $obj->toArray());
        $obj->push([3]);
        $this->assertEquals([-1, 0, 1, 2, 3], $obj->toArray());
        $obj->push([4, 5]);
        $this->assertEquals([-1, 0, 1, 2, 3, 4, 5], $obj->toArray());
    }

    function testUnShift() {
        $a = [-1, 0, 1];
        $obj = new ArrayLikeObject($a);
        $obj->unShift(-2);
        $this->assertEquals([-2, -1, 0, 1], $obj->toArray());
        $obj->unShift([-3]);
        $this->assertEquals([-3, -2, -1, 0, 1], $obj->toArray());
        $obj->unShift([-5, -4]);
        $this->assertEquals([-5, -4, -3, -2, -1, 0, 1], $obj->toArray());
    }

    function testPop() {
        $a = [0, 1, 2, 3, 4, 5];
        $obj = new ArrayLikeObject($a);

        $this->assertEquals(5, $obj->pop());
        $this->assertEquals([0, 1, 2, 3, 4], $obj->toArray());

        $this->assertEquals([4, 3], $obj->pop(2));
        $this->assertEquals([0, 1, 2], $obj->toArray());
    }

    function testShift() {
        $a = [-5, -4, -3, -2, -1, 0];
        $obj = new ArrayLikeObject($a);

        $this->assertEquals(-5, $obj->shift());
        $this->assertEquals([-4, -3, -2, -1, 0], $obj->toArray());

        $this->assertEquals([-4, -3], $obj->shift(2));
        $this->assertEquals([-2, -1, 0], $obj->toArray());
    }
}

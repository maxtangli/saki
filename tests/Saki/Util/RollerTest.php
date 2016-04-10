<?php

use Saki\Util\Roller;

class RollerTest extends PHPUnit_Framework_TestCase {
    function testRoll() {
        $r = new Roller([1, 2, 3, 4]);
        $this->assertEquals(1, $r->getCircleCount());

        $data = [
            [2, 1, 1], [3, 1, 1], [4, 1, 1],
            [1, 2, 2], [3, 2, 2],
            [2, 3, 2], [4, 3, 2],
            [3, 4, 3],
        ];
        foreach ($data as list($target, $expectedCircleCount, $expectedLocalTurn)) {
            $r->toTarget($target);
            $this->assertEquals($target, $r->getCurrentTarget());
            $this->assertEquals($expectedCircleCount, $r->getCircleCount(), 'global');
            $this->assertEquals($expectedLocalTurn, $r->getTargetLocalTurn($target), 'local');
        }
    }

    function testGetOffsetTarget() {
        $r = new Roller([1, 2, 3, 4]);
        $r->toTarget(2);
        $data = [
            [0, 2], [4, 2], [8, 2], [-4, 2], [-8, 2],
            [1, 3], [5, 3], [9, 3], [-3, 3], [-7, 3],
        ];
        foreach ($data as list($offset, $expected)) {
            $this->assertEquals($expected, $r->getOffsetTarget($offset));
        }
    }
}

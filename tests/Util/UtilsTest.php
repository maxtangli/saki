<?php


class UtilsTest extends PHPUnit_Framework_TestCase {
    function testArrayMax() {
        $selector = function ($v) {
            return $v;
        };
        $a = [1, 2, 5, 4, 3];
        $this->assertEquals(5, \Saki\Util\Utils::array_max($a, $selector));
        $this->assertEquals(1, \Saki\Util\Utils::array_min($a, $selector));
    }
}

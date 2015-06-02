<?php


class HandTest extends PHPUnit_Framework_TestCase {

    function testConstructor() {
        $h = new \Saki\Hand(
            [
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
            ]
        );
        foreach($h as $t) {
            $this->assertEquals('1m', $t->__toString());
        }
    }

    function testToString() {
        // order like 123m456p789s東東東中中
        $h = new \Saki\Hand(
            [
                new \Saki\Tile(1, \Saki\SuitConst::BAMBOO),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::DOT),
                new \Saki\Tile(\Saki\HonorConst::EAST),
                new \Saki\Tile(\Saki\HonorConst::RED),
            ]
        );
        $this->assertEquals('1m1p1s東中', $h->__toString());
        // short write
        $h = new \Saki\Hand(
            [
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
            ]
        );
        $this->assertEquals('111m', $h->__toString());
        // short write with Honor
        $h = new \Saki\Hand(
            [
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(1, \Saki\SuitConst::CHARACTER),
                new \Saki\Tile(\Saki\HonorConst::EAST),
            ]
        );
        $this->assertEquals('111m東', $h->__toString());
    }
}
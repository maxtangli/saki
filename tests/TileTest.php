<?php


class TileTest extends PHPUnit_Framework_TestCase {

    function testConstructor() {
        $m = new \Saki\Tile(1, \Saki\SuitConst::CHARACTER);
        $this->assertEquals(1, $m->getNumber());
        $this->assertEquals(\Saki\SuitConst::CHARACTER, $m->getSuit());
        $this->assertEquals('1m', $m->__toString());

        $m = new \Saki\Tile(\Saki\HonorConst::EAST);
        $this->assertEquals(\Saki\HonorConst::EAST, $m->getHonor());
        $this->assertEquals('東', $m->__toString());
    }
}
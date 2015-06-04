<?php

use \Saki\Tile\Tile;
use Saki\Tile\TileType;

class TileTest extends PHPUnit_Framework_TestCase {

    function testOverall() {
        $m = new Tile(TileType::fromString('m'), 1);
        $this->assertEquals(1, $m->getNumber());
        $this->assertEquals(TileType::CHARACTER, $m->getTileType()->getValue());
        $this->assertEquals('1m', $m->__toString());

        $m = new Tile(TileType::fromString('E'));
        $this->assertEquals(TileType::EAST, $m->getTileType()->getValue());
        $this->assertEquals('E', $m->__toString());
    }

    function testIdentity() {
        $m1 = new Tile(TileType::fromString('m'), 1);
        $m2 = new Tile(TileType::fromString('m'), 1);
        $this->assertEquals($m1, $m2);
        $this->assertNotSame($m1, $m2);
        $m3 = new Tile(TileType::fromString('m'), 2);
        $this->assertNotEquals($m1, $m3);
    }

    function testFromString() {
        $m1 = new Tile(TileType::fromString('m'), 1);
        $m2 = Tile::fromString('1m');
        $this->assertEquals($m1, $m2);

        $m3 = new Tile(TileType::fromString('E'));
        $m4 = Tile::fromString('E');
        $this->assertEquals($m3, $m4);
    }
}
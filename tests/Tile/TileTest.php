<?php

use Saki\Tile\Tile;
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

    /**
     * @dataProvider nextTileProvider
     */
    function testNextTile($tileString, $offset, $nextTileString) {
        $tile = Tile::fromString($tileString);
        $nextTile = Tile::fromString($nextTileString);
        $result = $tile->toNextTile($offset);
        $this->assertEquals($nextTile, $result, sprintf('%s->toNextTile(%s) expected %s but actual %s', $tile, $offset, $nextTile, $result));
    }

    function nextTileProvider() {
        return [
            ['1s', 8, '9s'],
            ['1s', 9, '1s'],['1s', 10, '2s'],['1s', 17, '9s'],['1s', 18, '1s'],['1s', 19, '2s'],

            ['E', 1, 'S'],
            ['E', 3, 'N'],['E', 4, 'E'],['E', 5, 'S'],['E', 6, 'W'],['E', 7, 'N'],['E', 8, 'E'],
        ];
    }
}
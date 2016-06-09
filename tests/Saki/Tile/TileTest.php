<?php

use Saki\Tile\Tile;
use Saki\Tile\TileType;

class TileTest extends \SakiTestCase {
    function testCompare() {
        $t5m = Tile::fromString('5m');
        $t0m = Tile::fromString('0m');
        $this->assertEquals(1, $t0m->compareTo($t5m));
        $this->assertEquals(-1, $t5m->compareTo($t0m));

        $f = Tile::getComparator();
        $this->assertEquals(1, $f($t0m, $t5m));
        $this->assertEquals(-1, $f($t5m, $t0m));
    }

    function testEqual() {
        $t5m = Tile::fromString('5m');
        $t0m = Tile::fromString('0m');
        $this->assertFalse($t0m->equalTo($t5m, true));
        $this->assertTrue($t0m->equalTo($t5m, false));

        $f = Tile::getEqual(true);
        $this->assertFalse($f($t0m, $t5m));

        $f = Tile::getEqual(false);
        $this->assertTrue($f($t0m, $t5m));
    }

    function testOverall() {
        $m = Tile::create(TileType::fromString('m'), 1);
        $this->assertEquals(1, $m->getNumber());
        $this->assertEquals(TileType::CHARACTER_M, $m->getTileType()->getValue());
        $this->assertEquals('1m', $m->__toString());

        $m = Tile::create(TileType::fromString('E'));
        $this->assertEquals(TileType::EAST_E, $m->getTileType()->getValue());
        $this->assertEquals('E', $m->__toString());
    }

    function testIdentity() {
        $m1 = Tile::create(TileType::fromString('m'), 1);
        $m2 = Tile::create(TileType::fromString('m'), 1);
        $this->assertEquals($m1, $m2);
        $this->assertSame($m1, $m2);
        $m3 = Tile::create(TileType::fromString('m'), 2);
        $this->assertNotEquals($m1, $m3);
    }

    function testFromString() {
        $m1 = Tile::create(TileType::fromString('m'), 1);
        $m2 = Tile::fromString('1m');
        $this->assertEquals($m1, $m2);

        $m3 = Tile::create(TileType::fromString('E'));
        $m4 = Tile::fromString('E');
        $this->assertEquals($m3, $m4);
    }

    function testIsRedDora() {
        $normal5m = Tile::create(TileType::create(TileType::CHARACTER_M), 5, false);
        $this->assertFalse($normal5m->isRedDora());

        $red5m = Tile::create(TileType::create(TileType::CHARACTER_M), 5, true);
        $this->assertTrue($red5m->isRedDora());

        $this->assertEquals($normal5m, $red5m);
        $this->assertNotSame($normal5m, $red5m);

        $red5p = Tile::create(TileType::create(TileType::DOT_P), 5, true);
        $this->assertTrue($red5p->isRedDora());
        $this->assertNotEquals($red5p, $red5m);
        $this->assertNotSame($red5p, $red5m);
    }

    function testRedDoraNumber() {
        $r5m = Tile::fromString('0m');
        $this->assertEquals(5, $r5m->getNumber());
    }

    function testRedDoraString() {
        $m5 = Tile::create(TileType::fromString('m'), 5, true);
        $m6 = Tile::fromString('0m');
        $this->assertEquals($m5, $m6);
        $this->assertTrue($m6->isRedDora());

        $this->assertEquals('0m', $m5);
        $this->assertEquals('0m', $m6);

        $this->assertEquals('5m', $m5->toFormatString(false));
    }

    /**
     * @dataProvider nextTileProvider
     */
    function testNextTile($tileString, $offset, $nextTileString) {
        $tile = Tile::fromString($tileString);
        $nextTile = Tile::fromString($nextTileString);
        $result = $tile->getNextTile($offset);
        $this->assertEquals($nextTile, $result, sprintf('%s->toNextTile(%s) expected %s but actual %s', $tile, $offset, $nextTile, $result));
    }

    function nextTileProvider() {
        return [
            ['1s', 8, '9s'],
            ['1s', 9, '1s'], ['1s', 10, '2s'], ['1s', 17, '9s'], ['1s', 18, '1s'], ['1s', 19, '2s'],

            ['E', 1, 'S'],
            ['E', 3, 'N'], ['E', 4, 'E'], ['E', 5, 'S'], ['E', 6, 'W'], ['E', 7, 'N'], ['E', 8, 'E'],
        ];
    }
}
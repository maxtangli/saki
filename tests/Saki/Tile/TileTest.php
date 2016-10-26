<?php

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileType;

class TileTest extends \SakiTestCase {
    function testCompare() {
        $normal5m = Tile::fromString('5m');
        $red5m = Tile::fromString('0m');

        $this->assertEquals(1, $red5m->compareTo($normal5m));
        $this->assertEquals(-1, $normal5m->compareTo($red5m));

        $f = Tile::getComparator();
        $this->assertEquals(1, $f($red5m, $normal5m));
        $this->assertEquals(-1, $f($normal5m, $red5m));

        $this->assertTrue($normal5m == $red5m);
        $this->assertFalse($normal5m === $red5m);

        $a = [$normal5m, $red5m, $normal5m, $red5m];
        $this->assertEquals([$normal5m, $red5m], array_unique([$normal5m, $red5m, $normal5m, $red5m]));
    }

    function testOverall() {
        $m = Tile::fromString('1m');
        $this->assertEquals(1, $m->getNumber());
        $this->assertEquals(TileType::CHARACTER_M, $m->getTileType()->getValue());
        $this->assertEquals('1m', $m->__toString());

        $m = Tile::fromString('E');
        $this->assertEquals(TileType::EAST_E, $m->getTileType()->getValue());
        $this->assertEquals('E', $m->__toString());
    }

    function testIdentity() {
        $m1 = Tile::fromString('1m');
        $m2 = Tile::fromString('1m');
        $this->assertEquals($m1, $m2);
        $this->assertSame($m1, $m2);
        $m3 = Tile::fromString('2m');;
        $this->assertNotEquals($m1, $m3);
    }

    function testRedDoraString() {
        $red5m = Tile::fromString('0m');
        $this->assertTrue($red5m->isRedDora());
        $this->assertEquals(5, $red5m->getNumber());
        $this->assertEquals('0m', $red5m->toFormatString(true));
        $this->assertEquals('5m', $red5m->toFormatString(false));

        $normal5m = Tile::fromString('5m');
        $this->assertFalse($normal5m->isRedDora());
        $this->assertEquals($normal5m, $red5m);
        $this->assertNotSame($normal5m, $red5m);

        $red5p = Tile::fromString('0p');
        $this->assertTrue($red5p->isRedDora());
        $this->assertNotEquals($red5p, $red5m);
        $this->assertNotSame($red5p, $red5m);
    }

    /**
     * @dataProvider nextTileProvider
     */
    function testNextTile(string $tileString, int $offset, string $nextTileString) {
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
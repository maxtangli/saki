<?php

use Saki\Tile;
use Saki\TileList;

class TileListTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider validStringProvider
     */
    function testValidString($s, $expected) {
        $this->assertSame($expected, TileList::validString($s), "\$s[$s]");
    }

    function validStringProvider() {
        return [
            ['1m', true],
            ['11m', true],
            ['E11mE', true],
            ['11sE12s123123sESWNCN', true],
            ['', true],
            ['1m1', false],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    function testFromString($s, $tiles) {
        $this->assertEquals($tiles, iterator_to_array(TileList::fromString($s, false)->getIterator()));
    }

    function fromStringProvider() {
        return [
            ['1m', [Tile::fromString('1m')]],
            ['11m', [Tile::fromString('1m'), Tile::fromString('1m')]],
            ['E11mE', [Tile::fromString('E'), Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('E')]],
        ];
    }

    function testConstructor() {
        $h = new TileList([
            Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m'),
        ]);
        foreach ($h as $t) {
            $this->assertSame('1m', $t->__toString());
        }
    }

    function testOrderKeep() {
        $tiles = [
            Tile::fromString('3m'), Tile::fromString('1m'), Tile::fromString('2m'),
        ];
        $h = new TileList($tiles);
        for ($i = 0; $i < count($tiles); ++$i) {
            $this->assertEquals($tiles[$i], $h[$i]);
        }
    }

    /**
     * @dataProvider toStringProvider
     */
    function testToString(array $tileStrings, $expected) {
        $tiles = array_map(function($v){return Tile::fromString($v);}, $tileStrings);
        $h = new Saki\TileList($tiles);
        $this->assertSame($expected, $h->__toString());
    }

    function toStringProvider() {
        return [
            [['1m', '1p', '1s', 'E', 'C'], '1m1p1sEC'], // keep order
            [['1m', '1m', '1m',], '111m'], // short write
            [['1m', '1m', '1m', 'E'], '111mE'],  // short write with Honor
        ];
    }

    function testAdd() {
        $l = TileList::fromString('1m', false);
        $l->add(Tile::fromString('2m'));
        $this->assertSame('12m', $l->__toString());
    }

    function testReplace() {
        $l = TileList::fromString('11m', false);
        $l->replaceTile(Tile::fromString('1m'), Tile::fromString('2m'));
        $this->assertSame('21m', $l->__toString());
    }

    function testRemove() {
        $l = TileList::fromString('12322m', false);
        $l->removeTile(Tile::fromString('2m'));
        $this->assertEquals('1322m', $l->__toString());
        $expectedKey = 0;
        foreach($l as $k => $v) {
            $this->assertEquals($expectedKey++, $k);
        }
    }

    function testRemoveMany() {
        $l = TileList::fromString('123242m', false);
        $l->removeManyTiles([Tile::fromString('2m'),Tile::fromString('2m')]);
        $this->assertEquals('1342m', $l->__toString());
    }
}

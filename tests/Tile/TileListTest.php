<?php

use Saki\Tile\Tile;
use Saki\Tile\TileList;

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
            ['0m', true],
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
            ['0m', [Tile::fromString('0m')]],
            ['05m', [Tile::fromString('0m'), Tile::fromString('5m')]],
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
        $tiles = array_map(function ($v) {
            return Tile::fromString($v);
        }, $tileStrings);
        $h = new \Saki\Tile\TileList($tiles);
        $this->assertSame($expected, $h->__toString());
    }

    function toStringProvider() {
        return [
            [['1m', '1p', '1s', 'E', 'C'], '1m1p1sEC'], // keep order
            [['1m', '1m', '1m',], '111m'], // short write
            [['1m', '1m', '1m', 'E'], '111mE'],  // short write with Honor
        ];
    }

    function testSort() {
        $expected = '1234506789m1234506789p1234506789sESWNCPF';
        $l = TileList::fromString($expected);
        $this->assertEquals($expected, $l->__toString());

        $this->assertEquals('50m50p50s', TileList::fromString('05p50m05s')->sort()->__toString());

        $shuffled = 'WNC1206783459m1234578069p1234578069sESPF';
        $l = TileList::fromString($shuffled);
        $l->sort();
        $this->assertEquals($expected, $l->__toString());
    }
}

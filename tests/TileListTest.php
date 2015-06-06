<?php

use Saki\Tile;
use Saki\TileList;

class TileListTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider validStringProvider
     */
    function testValidString($s, $expected) {
        $this->assertEquals($expected, TileList::validString($s), "$s");
    }

    function validStringProvider() {
        return [
            ['1m', true],
            ['11m', true],
            ['E11mE', true],
            ['11sE12s123123sESWNCN', true],
            ['', false],
            ['1m1', false],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    function testFromString($s, $tiles) {
        $this->assertEquals($tiles, iterator_to_array(TileList::fromString($s)->getIterator()));
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
            $this->assertEquals('1m', $t->__toString());
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
        $this->assertEquals($expected, $h->__toString());
    }

    function toStringProvider() {
        return [
            [['1m', '1p', '1s', 'E', 'C'], '1m1p1sEC'], // keep order
            [['1m', '1m', '1m',], '111m'], // short write
            [['1m', '1m', '1m', 'E'], '111mE'],  // short write with Honor
        ];
    }

    function testReplace() {

    }

    function testRemove() {

    }
}

<?php

use Saki\Tile;
use Saki\TileList;

class TileOrderedListTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $tiles = [
            Tile::fromString('3m'), Tile::fromString('1m'), Tile::fromString('2m'),
        ];
        $expectedTiles = [
            Tile::fromString('1m'), Tile::fromString('2m'), Tile::fromString('3m'),
        ];
        $h = new \Saki\TileOrderedList($tiles);
        for ($i = 0; $i < count($tiles); ++$i) {
            $this->assertEquals($expectedTiles[$i], $h[$i]);
        }
    }

    /**
     * @depends testOverall
     * @dataProvider toStringProvider
     */
    function testToString(array $tileStrings, $expected) {
        // order like 123m456p789s東東東中中
        $tiles = array_map(function($v){return Tile::fromString($v);}, $tileStrings);
        $h = new Saki\TileOrderedList($tiles);
        $this->assertEquals($expected, $h->__toString());
    }

    function toStringProvider() {
        return [
            [['1m', 'C', '1s', 'E', '1p',], '1m1p1sEC'], // sort type
            [['3m', '1m', '2m',], '123m'], // sort number
            [['1m', 'C', '1s','3m', '1m', '2m', 'E', '1p',], '1123m1p1sEC'],  // sort number and type
        ];
    }
}
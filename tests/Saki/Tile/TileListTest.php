<?php

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileType;
use Saki\Util\Utils;

class TileListTest extends \SakiTestCase {
    /**
     * @dataProvider validStringProvider
     */
    function testValidString(bool $expected, string $tileList) {
        $this->assertBool($expected, TileList::validString($tileList));
    }

    function validStringProvider() {
        return [
            [true, '1m'],
            [true, '11m'],
            [true, 'E11mE'],
            [true, '11sE12s123123sESWNCN'],
            [true, ''],
            [false, '1m1'],
            [true, '0m'],
        ];
    }

    /**
     * @dataProvider fromStringProvider
     */
    function testFromString(array $expectedTiles, string $tileList) {
        $this->assertEquals($expectedTiles,
            TileList::fromString($tileList)->toArray(Utils::getToStringCallback()));
    }

    function fromStringProvider() {
        return [
            [['1m'], '1m'],
            [['1m', '1m'], '11m'],
            [['E', '1m', '1m', 'E'], 'E11mE'],
            [['0m'], '0m'],
            [['0m', '5m'], '05m'],
        ];
    }

    function testFromNumbers() {
        $this->assertEquals('123s', TileList::fromNumbers([1, 2, 3], TileType::fromString('s'))->__toString());
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
    function testToString(string $expected, array $tileStrings) {
        $toTile = function ($v) {
            return Tile::fromString($v);
        };
        $tiles = array_map($toTile, $tileStrings);
        $tileList = new TileList($tiles);
        $this->assertSame($expected, $tileList->__toString());
    }

    function toStringProvider() {
        return [
            ['1m1p1sEC', ['1m', '1p', '1s', 'E', 'C']], // keep order
            ['111m', ['1m', '1m', '1m',]], // short write
            ['111mE', ['1m', '1m', '1m', 'E']],  // short write with Honour
        ];
    }

    function testSort() {
        $expected = '1234506789m1234506789p1234506789sESWNCPF';
        $l = TileList::fromString($expected);
        $this->assertEquals($expected, $l->__toString());

        $this->assertEquals('50m50p50s', TileList::fromString('05p50m05s')->orderByTileID()->__toString());

        $shuffled = 'WNC1206783459m1234578069p1234578069sESPF';
        $l = TileList::fromString($shuffled);
        $l->orderByTileID();
        $this->assertEquals($expected, $l->__toString());
    }
}

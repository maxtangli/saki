<?php

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MeldTypeTest extends PHPUnit_Framework_TestCase {

    function testToString() {
        $this->assertSame('SingleMeldType', \Saki\Meld\SingleMeldType::getInstance()->__toString());
    }

    function testSingle() {
        $mt = \Saki\Meld\SingleMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('1m')));
        $this->assertFalse($mt->valid(TileList::fromString('11m')));
    }

    function testEyes() {
        $mt = \Saki\Meld\PairMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('11m')));
        $this->assertFalse($mt->valid(TileList::fromString('111m')));
    }

    function testSequence() {
        $mt = \Saki\Meld\RunMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('123m')));
        $this->assertTrue($mt->valid(TileList::fromString('132m')));
        $this->assertTrue($mt->valid(TileList::fromString('213m')));
        $this->assertTrue($mt->valid(TileList::fromString('231m')));
        $this->assertTrue($mt->valid(TileList::fromString('312m')));
        $this->assertTrue($mt->valid(TileList::fromString('321m')));
        $this->assertFalse($mt->valid(TileList::fromString('12m3s')));
        $this->assertFalse($mt->valid(TileList::fromString('121m')));
    }

    function testTriplet() {
        $mt = \Saki\Meld\TripleMeldType::getInstance();
        $this->assertTrue($mt->valid(TileList::fromString('111m')));
        $this->assertFalse($mt->valid(TileList::fromString('11m1s')));
        $this->assertFalse($mt->valid(TileList::fromString('11m')));
    }

    /**
     * @dataProvider weakRunProvider
     */
    function testWeakRun($tileListString, array $waitingTileStrings) {
        $mt = \Saki\Meld\WeakRunMeldType::getInstance();
        $tileList = TileList::fromString($tileListString);
        $waitingTiles = array_map(function ($s) {
            return Tile::fromString($s);
        }, $waitingTileStrings);
        if (!empty($waitingTiles)) {
            $this->assertTrue($mt->valid($tileList));
            $this->assertEquals($waitingTiles, $mt->getWaitingTiles($tileList));
        } else {
            $this->assertFalse($mt->valid($tileList));
        }
    }

    function weakRunProvider() {
        return [
            ['12m', ['3m']],
            ['89m', ['7m']],
            ['13m', ['2m']],
            ['23m', ['1m', '4m']],
            ['14m', []],
        ];
    }
}
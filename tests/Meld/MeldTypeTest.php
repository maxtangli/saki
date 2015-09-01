<?php

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;
use Saki\Win\WaitingType;
use Saki\Meld\Meld;

class MeldTypeTest extends PHPUnit_Framework_TestCase {

    function testToString() {
        $this->assertSame('PairMeldType', \Saki\Meld\PairMeldType::getInstance()->__toString());
    }

    function testPair() {
        $mt = \Saki\Meld\PairMeldType::getInstance();
        $this->assertTrue($mt->valid(TileSortedList::fromString('11m')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('111m')));
    }

    function testRun() {
        $mt = \Saki\Meld\RunMeldType::getInstance();
        $this->assertTrue($mt->valid(TileSortedList::fromString('123m')));
        $this->assertTrue($mt->valid(TileSortedList::fromString('132m')));
        $this->assertTrue($mt->valid(TileSortedList::fromString('213m')));
        $this->assertTrue($mt->valid(TileSortedList::fromString('231m')));
        $this->assertTrue($mt->valid(TileSortedList::fromString('312m')));
        $this->assertTrue($mt->valid(TileSortedList::fromString('321m')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('12m3s')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('121m')));
    }

    function testTriple() {
        $mt = \Saki\Meld\TripleMeldType::getInstance();
        $this->assertTrue($mt->valid(TileSortedList::fromString('111m')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('11m1s')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('11m')));
    }

    function testWinSetType() {
        $this->assertTrue(\Saki\Meld\RunMeldType::getInstance()->getWinSetType()->isWinSet());
        $this->assertTrue(\Saki\Meld\RunMeldType::getInstance()->getWinSetType()->isHandWinSet());

        $this->assertTrue(\Saki\Meld\QuadMeldType::getInstance()->getWinSetType()->isWinSet());
        $this->assertFalse(\Saki\Meld\QuadMeldType::getInstance()->getWinSetType()->isHandWinSet());
    }

    // --- weak ---

    function testWeakPair() {
        $mt = \Saki\Meld\WeakPairMeldType::getInstance();
        $this->assertTrue($mt->valid(TileSortedList::fromString('1m')));
        $this->assertFalse($mt->valid(TileSortedList::fromString('11m')));
    }

    /**
     * @dataProvider weakRunProvider
     */
    function testWeakRun($tileListString, array $waitingTileStrings, $waitingTypeValue) {
        $mt = \Saki\Meld\WeakRunMeldType::getInstance();
        $tileSortedList = \Saki\Tile\TileSortedList::fromString($tileListString);
        $waitingTiles = array_map(function ($s) {
            return Tile::fromString($s);
        }, $waitingTileStrings);
        if (!empty($waitingTiles)) {
            $this->assertTrue($mt->valid($tileSortedList));
            $this->assertEquals($waitingTiles, $mt->getWaitingTiles($tileSortedList));
            $waitingType = WaitingType::getInstance($waitingTypeValue);
            $this->assertEquals($waitingType, $mt->getWaitingType($tileSortedList), $mt->getWaitingType($tileSortedList));

            $weakRunMeld = Meld::fromString($tileListString);
            foreach($waitingTiles as $waitingTile) {
                $this->assertTrue($weakRunMeld->canToTargetMeld($waitingTile));
                $targetMeld = $weakRunMeld->toTargetMeld($waitingTile);
                $this->assertTrue($targetMeld->canToWeakMeld($waitingTile));
                $this->assertEquals($weakRunMeld, $targetMeld->toWeakMeld($waitingTile));
            }
        } else {
            $this->assertFalse($mt->valid($tileSortedList));
        }
    }

    function weakRunProvider() {
        return [
            ['12m', ['3m'], WaitingType::ONE_SIDE_RUN_WAITING],
            ['89m', ['7m'], WaitingType::ONE_SIDE_RUN_WAITING],
            ['13m', ['2m'], WaitingType::MIDDLE_RUN_WAITING],
            ['23m', ['1m', '4m'], WaitingType::TWO_SIDE_RUN_WAITING],
            ['14m', [], null],
        ];
    }
}
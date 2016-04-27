<?php

use Saki\Meld\Meld;
use Saki\Meld\MeldType;
use Saki\Meld\PairMeldType;
use Saki\Meld\QuadMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\ThirteenOrphanMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Waiting\WaitingType;

class MeldTypeTest extends PHPUnit_Framework_TestCase {
    function testToString() {
        $this->assertSame('PairMeldType', PairMeldType::create()->__toString());
    }

    function testWinSetType() {
        $this->assertTrue(RunMeldType::create()->getWinSetType()->isWinSet());
        $this->assertTrue(RunMeldType::create()->getWinSetType()->isHandWinSet());

        $this->assertTrue(QuadMeldType::create()->getWinSetType()->isWinSet());
        $this->assertFalse(QuadMeldType::create()->getWinSetType()->isHandWinSet());
    }

    /**
     * @dataProvider validProvider
     */
    function testValid(bool $valid, MeldType $mt, string $tileListString) {
        $l = TileList::fromString($tileListString);
        if ($valid) {
            $this->assertTrue($mt->valid($l), sprintf('%s,%s', $mt, $tileListString));
        } else {
            $this->assertFalse($mt->valid($l), sprintf('%s,%s', $mt, $l));
        }
    }

    function validProvider() {
        $pair = PairMeldType::create();
        $run = RunMeldType::create();
        $triple = TripleMeldType::create();
        $weakPair = WeakPairMeldType::create();
        $weakRun = WeakRunMeldType::create();
        $orphan = ThirteenOrphanMeldType::create();
        return [
            [true, $pair, '11m'],
            [false, $pair, '111m'],

            [true, $triple, '111m'],
            [false, $triple, '11m1s'],
            [false, $triple, '11m'],

            [true, $run, '123m'],
            [true, $run, '132m'],
            [true, $run, '213m'],
            [true, $run, '231m'],
            [true, $run, '312m'],
            [true, $run, '321m'],
            [false, $run, '12m3s'],
            [false, $run, '121m'],

            [true, $weakPair, '1m'],
            [false, $weakPair, '11m'],

            [true, $weakRun, '12m'],
            [true, $weakRun, '89m'],
            [true, $weakRun, '13m'],
            [true, $weakRun, '23m'],
            [false, $weakRun, '14m'],

            [true, $orphan, '119m19p19sESWNCPF'],
            [true, $orphan, '199m19p19sESWNCPF'],
            [true, $orphan, '9m19m19p19sESWNCPF'],
            [false, $orphan, '19m19p19sESWNCPF'],
        ];
    }

    /**
     * @dataProvider weakRunProvider
     */
    function testWeakRun($tileListString, array $waitingTileStrings, $waitingTypeValue) {
        $weakRun = WeakRunMeldType::create();
        $tileList = TileList::fromString($tileListString);
        $waitingTileList = (new ArrayList($waitingTileStrings))->select(function ($s) {
            return Tile::fromString($s);
        });
        $waitingType = WaitingType::create($waitingTypeValue);

        // test waitingTiles, waitingType
        $this->assertEquals($waitingTileList->toArray(), $weakRun->getWaitingTileList($tileList)->toArray());
        $this->assertEquals($waitingType, $weakRun->getWaitingType($tileList), $weakRun->getWaitingType($tileList));

        // test toTargetMeld todo
        $weakRunMeld = Meld::fromString($tileListString);
        foreach ($waitingTileList as $waitingTile) {
            $this->assertTrue($weakRunMeld->canToTargetMeld($waitingTile));
            $targetMeld = $weakRunMeld->toTargetMeld($waitingTile);
            $this->assertTrue($targetMeld->canToWeakMeld($waitingTile));
            $this->assertEquals($weakRunMeld, $targetMeld->toWeakMeld($waitingTile));
        }
    }

    function weakRunProvider() {
        return [
            ['12m', ['3m'], WaitingType::ONE_SIDE_RUN_WAITING],
            ['89m', ['7m'], WaitingType::ONE_SIDE_RUN_WAITING],
            ['13m', ['2m'], WaitingType::MIDDLE_RUN_WAITING],
            ['23m', ['1m', '4m'], WaitingType::TWO_SIDE_RUN_WAITING],
        ];
    }
}
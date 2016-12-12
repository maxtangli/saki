<?php

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldType;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\KongMeldType;
use Saki\Game\Meld\ChowMeldType;
use Saki\Game\Meld\ThirteenOrphanMeldType;
use Saki\Game\Meld\PungMeldType;
use Saki\Game\Meld\WeakPairMeldType;
use Saki\Game\Meld\WeakChowMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Waiting\WaitingType;

class MeldTypeTest extends \SakiTestCase {
    function testToString() {
        $this->assertSame('PairMeldType', PairMeldType::create()->__toString());
    }

    function testWinSetType() {
        $this->assertTrue(ChowMeldType::create()->getWinSetType()->isWinSet());
        $this->assertTrue(ChowMeldType::create()->getWinSetType()->isHandWinSet());

        $this->assertTrue(KongMeldType::create()->getWinSetType()->isWinSet());
        $this->assertFalse(KongMeldType::create()->getWinSetType()->isHandWinSet());
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
        $chow = ChowMeldType::create();
        $pung = PungMeldType::create();
        $weakPair = WeakPairMeldType::create();
        $weakChow = WeakChowMeldType::create();
        $orphan = ThirteenOrphanMeldType::create();
        return [
            [true, $pair, '11m'],
            [false, $pair, '111m'],

            [true, $pung, '111m'],
            [false, $pung, '11m1s'],
            [false, $pung, '11m'],

            [true, $chow, '123m'],
            [true, $chow, '132m'],
            [true, $chow, '213m'],
            [true, $chow, '231m'],
            [true, $chow, '312m'],
            [true, $chow, '321m'],
            [false, $chow, '12m3s'],
            [false, $chow, '121m'],

            [true, $weakPair, '1m'],
            [false, $weakPair, '11m'],

            [true, $weakChow, '12m'],
            [true, $weakChow, '89m'],
            [true, $weakChow, '13m'],
            [true, $weakChow, '23m'],
            [false, $weakChow, '14m'],

            [true, $orphan, '119m19p19sESWNCPF'],
            [true, $orphan, '199m19p19sESWNCPF'],
            [true, $orphan, '9m19m19p19sESWNCPF'],
            [false, $orphan, '19m19p19sESWNCPF'],
        ];
    }

    /**
     * @dataProvider weakChowProvider
     */
    function testWeakChow(string $tileListString, array $waitingTileStrings, int $waitingTypeValue) {
        $weakChow = WeakChowMeldType::create();
        $tileList = TileList::fromString($tileListString);
        $waitingTileList = (new ArrayList($waitingTileStrings))->select(function ($s) {
            return Tile::fromString($s);
        });
        $waitingType = WaitingType::create($waitingTypeValue);

        // test waitingTiles, waitingType
        $this->assertEquals($waitingTileList->toArray(), $weakChow->getWaiting($tileList)->toArray());
        $this->assertEquals($waitingType, $weakChow->getWaitingType($tileList), $weakChow->getWaitingType($tileList));

        // test toTargetMeld
        $weakChowMeld = Meld::fromString($tileListString);
        foreach ($waitingTileList as $waitingTile) {
            $this->assertTrue($weakChowMeld->canToTargetMeld($waitingTile));
            $targetMeld = $weakChowMeld->toTargetMeld($waitingTile);
            $this->assertTrue($targetMeld->canToWeakMeld($waitingTile));
            $this->assertEquals($weakChowMeld, $targetMeld->toWeakMeld($waitingTile));
        }
    }

    function weakChowProvider() {
        return [
            ['12m', ['3m'], WaitingType::ONE_SIDE_CHOW_WAITING],
            ['89m', ['7m'], WaitingType::ONE_SIDE_CHOW_WAITING],
            ['13m', ['2m'], WaitingType::MIDDLE_CHOW_WAITING],
            ['23m', ['1m', '4m'], WaitingType::TWO_SIDE_CHOW_WAITING],
        ];
    }
}
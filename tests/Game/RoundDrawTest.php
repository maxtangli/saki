<?php

use Saki\Game\MockRound;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Game\RoundPhase;

class RoundDrawTest extends PHPUnit_Framework_TestCase {
    function testExhaustiveDraw() {
        $r = new MockRound();
        // 130ms = avg0.7ms/time * 200times
        for ($phase = $r->getRoundPhase(); $phase != RoundPhase::getOverPhaseInstance(); $phase = $r->getRoundPhase()) {
            $r->discard($r->getCurrentPlayer(), $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()[0]);
            $r->passPublicPhase();
        }
        $cls = get_class(new \Saki\RoundResult\ExhaustiveDrawRoundResult(\Saki\Game\PlayerList::createStandard()->toArray(), [false, false, false, false]));
        $this->assertInstanceOf($cls, $r->getRoundData()->getTurnManager()->getRoundResult());
    }

    function testNineKindsOfTerminalOrHonorDraw() {
        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTerminalOrHonor());

        $r = new MockRound();
        $r->debugSetHandTileList($r->getCurrentPlayer(), $validTileList);
        $r->nineKindsOfTerminalOrHonorDraw($r->getCurrentPlayer());
        $this->assertEquals(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
    }

    // nine, exception case

    function testFourWindDraw() {
        $r = new MockRound();
        $tileE = Tile::fromString('E');
        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tileE);
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tileE);
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tileE);
        $r->passPublicPhase();

        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tileE);
        $r->passPublicPhase();
        $this->assertEquals(RoundResultType::FOUR_WIND_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
    }

//    function testFourReachDraw() { // 2.29s
//        $r = new MockRound();
//        $tileList = TileList::fromString('123456789m12355s');
//        $tile = Tile::fromString('1s');
//
//        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList); // 500ms
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $this->assertEquals(RoundResultType::FOUR_REACH_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
//    }

    function testFourKongDraw() { // todo handle kongPublicPhase
        $r = new MockRound();
        $tile = Tile::fromString('1s');

        $r->debugKongBySelfByReplace($r->getCurrentPlayer(), $tile);
        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tile);
        $r->passPublicPhase();

        $r->debugKongBySelfByReplace($r->getCurrentPlayer(), $tile);
        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tile);
        $r->passPublicPhase();

        $r->debugKongBySelfByReplace($r->getCurrentPlayer(), $tile);
        $r->debugDiscardByReplace($r->getCurrentPlayer(), $tile);
        $r->passPublicPhase();

        $r->debugKongBySelfByReplace($r->getCurrentPlayer(), $tile);
        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
    }
}
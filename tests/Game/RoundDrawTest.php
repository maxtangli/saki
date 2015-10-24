<?php

use Saki\Game\MockRound;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class RoundDrawTest extends PHPUnit_Framework_TestCase {
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

    function testFourReachDraw() {
        $r = new MockRound();
        $tileList = TileList::fromString('123456789m12355s');
        $tile = Tile::fromString('1s');

        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
        $r->passPublicPhase();

        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
        $r->passPublicPhase();

        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
        $r->passPublicPhase();

        $r->debugReachByReplace($r->getCurrentPlayer(), $tile, $tileList);
        $r->passPublicPhase();
        $this->assertEquals(RoundResultType::FOUR_REACH_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
    }

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
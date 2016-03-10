<?php

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Round;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Game\RoundPhase;

class RoundDrawTest extends PHPUnit_Framework_TestCase {
    function testExhaustiveDraw() {
        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();
        // 130ms = avg0.7ms/time * 200times
        for ($phase = $r->getRoundPhase(); $phase != RoundPhase::getOverInstance(); $phase = $r->getRoundPhase()) {
            $pro->process('discard I I:s-1m:1m; passAll');
        }
        $cls = get_class(new \Saki\RoundResult\ExhaustiveDrawRoundResult(\Saki\Game\PlayerList::createStandard()->toArray(), [false, false, false, false]));
        $this->assertInstanceOf($cls, $r->getRoundData()->getPhaseState()->getRoundResult());
    }

    function testNineKindsOfTerminalOrHonorDraw() {
        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTerminalOrHonor());

        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();
        $pro->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertEquals(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW, $r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    // nine, exception case

    function testFourWindDraw() {
        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();

        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('discard S S:s-E:E; passAll');
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-E:E; passAll');
        $this->assertEquals(RoundResultType::FOUR_WIND_DRAW, $r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

//    function testFourReachDraw() { // 2.29s
//        $r = new Round();
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
        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();

        $tile = Tile::fromString('1s');

        // error for discard todo detailed error message instead of Not executable command[discard].
//        $pro->process('concealedKong I I:s-1111s:1s; discard I I:s-1s:1s; passAll');
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
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('1m'));
        $r->passPublicPhase();

        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW, $r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    // todo test
}
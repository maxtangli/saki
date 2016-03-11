<?php

use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\RoundResult\RoundResultType;
use Saki\Tile\TileList;

class RoundDrawTest extends PHPUnit_Framework_TestCase {
    function testExhaustiveDraw() {
        $r = new Round();
        $pro = $r->getProcessor();
        // 130ms = avg0.7ms/time * 200times
        for ($phase = $r->getPhaseState()->getRoundPhase(); $phase != RoundPhase::getOverInstance(); $phase = $r->getPhaseState()->getRoundPhase()) {
            $pro->process('discard I I:s-1m:1m; passAll');
        }
        $cls = get_class(new \Saki\RoundResult\ExhaustiveDrawRoundResult(\Saki\Game\PlayerList::createStandard()->toArray(), [false, false, false, false]));
        $this->assertInstanceOf($cls, $r->getPhaseState()->getRoundResult());
    }

    function testNineKindsOfTerminalOrHonorDraw() {
        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTerminalOrHonor());

        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertEquals(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW, $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    // nine, exception case

    function testFourWindDraw() {
        $r = new Round();
        $pro = $r->getProcessor();

        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('discard S S:s-E:E; passAll');
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-E:E; passAll');
        $this->assertEquals(RoundResultType::FOUR_WIND_DRAW, $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

//    function testFourReachDraw() { // 2.29s
//        $r = new Round();
//        $tileList = TileList::fromString('123456789m12355s');
//        $tile = Tile::fromString('1s');
//
//        $r->debugReachByReplace($r->getTurnManager()->getCurrentPlayer(), $tile, $tileList); // 500ms
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getTurnManager()->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getTurnManager()->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getTurnManager()->getCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $this->assertEquals(RoundResultType::FOUR_REACH_DRAW, $r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->getValue());
//    }

    function testFourKongDraw() { // todo handle kongPublicPhase
        $r = new Round();
        $pro = $r->getProcessor();

        $pro->process('concealedKong I I:s-1111s:1s; discard I I:s-1s:1s; passAll');
        $pro->process('concealedKong I I:s-1111s:1s; discard I I:s-1s:1s; passAll');
        $pro->process('concealedKong I I:s-1111s:1s; discard I I:s-1s:1s; passAll');
        $pro->process('concealedKong I I:s-1111s:1s; discard I I:s-1s:1s; passAll');

        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    // todo test others
}
<?php

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Tile\TileList;
use Saki\Win\Result\ExhaustiveDrawResult;
use Saki\Win\Result\ResultType;

class RoundDrawTest extends PHPUnit_Framework_TestCase {
    function testExhaustiveDraw() {
        $r = new Round();
        $pro = $r->getProcessor();
        // 130ms = avg0.7ms/time * 200times
        for ($phase = $r->getPhaseState()->getPhase(); $phase != Phase::createOver(); $phase = $r->getPhaseState()->getPhase()) {
            $pro->process('discard I I:s-1m:1m; passAll');
        }
        $cls = ExhaustiveDrawResult::class;
        $this->assertInstanceOf($cls, $r->getPhaseState()->getResult());
    }

    function testNineKindsOfTerminalOrHonorDraw() {
        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTerminalOrHonor());

        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertEquals(ResultType::NINE_NINE_DRAW, $r->getPhaseState()->getResult()->getResultType()->getValue());
    }

    // nine, exception case

    function testFourWindDraw() {
        $r = new Round();
        $pro = $r->getProcessor();

        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('discard S S:s-E:E; passAll');
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-E:E; passAll');
        $this->assertEquals(ResultType::FOUR_WIND_DRAW, $r->getPhaseState()->getResult()->getResultType()->getValue());
    }

//    function testFourReachDraw() { // 2.29s
//        $r = new Round();
//        $tileList = TileList::fromString('123456789m12355s');
//        $tile = Tile::fromString('1s');
//
//        $r->debugReachByReplace($r->getAreas()->tempGetCurrentPlayer(), $tile, $tileList); // 500ms
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getAreas()->tempGetCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getAreas()->tempGetCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $r->debugReachByReplace($r->getAreas()->tempGetCurrentPlayer(), $tile, $tileList);
//        $r->passPublicPhase();
//
//        $this->assertEquals(ResultType::FOUR_REACH_DRAW, $r->getRoundData()->getTurnManager()->getResult()->getResultType()->getValue());
//    }

    // fourKongDraw tested in KongConcernedTest
}
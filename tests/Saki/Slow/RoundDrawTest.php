<?php

use Saki\Game\Phase;
use Saki\Tile\TileList;
use Saki\Win\Result\ResultType;

class RoundDrawTest extends SakiTestCase {
    function testNineNineDraw() {
        $r = $this->getInitRound();
        $pro = $r->getProcessor();

        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTermOrHonour());

        $pro->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertResultType(ResultType::NINE_NINE_DRAW);
    }

    function testExhaustiveDraw() {
        $r = $this->getInitRound();
        $pro = $r->getProcessor();

        for ($phase = $r->getPhaseState()->getPhase(); $phase != Phase::createOver(); $phase = $r->getPhaseState()->getPhase()) {
            $pro->process('discard I I:s-1m:1m; passAll');
        }
        $this->assertResultType(ResultType::EXHAUSTIVE_DRAW);
    }

    // FourKongDraw tested in KongConcernedTest

//    function testFourRiichiDraw() { // comment out since slow
//        $r = $this->getInitRound();
//        $pro = $r->getProcessor();
//        $pro->process(
//            'mockHand E 123456789m12357s; reach E 7s; passAll',
//            'mockHand S 123456789m12357s; reach S 7s; passAll',
//            'mockHand W 123456789m12357s; reach W 7s; passAll',
//            'mockHand N 123456789m12357s; reach N 7s; passAll'
//        );
//        $this->assertResultType(ResultType::FOUR_REACH_DRAW);
//    }

    function testFourWindDraw() {
        $r = $this->getInitRound();
        $pro = $r->getProcessor();

        $pro->process('discard E E:s-E:E; passAll');
        $pro->process('discard S S:s-E:E; passAll');
        $pro->process('discard W W:s-E:E; passAll');
        $pro->process('discard N N:s-E:E; passAll');
        $this->assertResultType(ResultType::FOUR_WIND_DRAW);
    }
}
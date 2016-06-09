<?php

use Saki\Game\Phase;
use Saki\Tile\TileList;
use Saki\Win\Result\ResultType;

class RoundDrawTest extends \SakiTestCase {
    function testNineNineDraw() {
        $round = $this->getInitRound();

        $validTileList = TileList::fromString('19m19p15559sESWNC');
        $this->assertTrue($validTileList->isNineKindsOfTermOrHonour());

        $round->process('mockHand E 19m19p15559sESWNC; nineNineDraw E');
        $this->assertResultType(ResultType::NINE_NINE_DRAW);
    }

    function testExhaustiveDraw() {
        $round = $this->getInitRound();

        for ($phase = $round->getPhaseState()->getPhase(); $phase != Phase::createOver(); $phase = $round->getPhaseState()->getPhase()) {
            $round->process('skip 1');
        }
        $this->assertResultType(ResultType::EXHAUSTIVE_DRAW);
    }

    // FourKongDraw tested in KongConcernedTest

//    function testFourRiichiDraw() { // comment out since slow
//        $round = $this->getInitRound();
//        
//        $round->process(
//            'mockHand E 123456789m12357s; reach E 7s; passAll',
//            'mockHand S 123456789m12357s; reach S 7s; passAll',
//            'mockHand W 123456789m12357s; reach W 7s; passAll',
//            'mockHand N 123456789m12357s; reach N 7s; passAll'
//        );
//        $this->assertResultType(ResultType::FOUR_REACH_DRAW);
//    }

    function testFourWindDraw() {
        $round = $this->getInitRound();

        $round->process('mockHand E E; discard E E; passAll');
        $round->process('mockHand S E; discard S E; passAll');
        $round->process('mockHand W E; discard W E; passAll');
        $round->process('mockHand N E; discard N E; passAll');
        $this->assertResultType(ResultType::FOUR_WIND_DRAW);
    }
}
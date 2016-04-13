<?php

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class RoundWinTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        // setup
        $r = new Round();
        $pro = $r->getProcessor();
        // setup
        $r->getAreas()->debugSetPrivate($r->getAreas()->getCurrentSeatWind(), TileList::fromString('123m456m789m123s55s'));
        // execute
        $pro->process('winBySelf E');
        // phase changed
        $this->assertEquals(Phase::create(Phase::OVER_PHASE), $r->getPhaseState()->getPhase());
        // point changed
        $dealer = SeatWind::createEast();
        // test toNextRound
        $this->assertEquals(Phase::createOver(), $r->getPhaseState()->getPhase());
        $r->toNextRound();
        $this->assertEquals(Phase::createPrivate(), $r->getPhaseState()->getPhase());
        // todo assert private state

        $this->assertEquals($dealer, $r->getAreas()->getDealerArea()->getPlayer()->getInitialSeatWind());
        // todo test initial state
    }

    function testWinByOther() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard E E:s-4s:4s; mockHand S 123m456m789m23s55s; winByOther S');
        $this->assertTrue($r->getPhaseState()->getResult()->getResultType()->isWin());
    }

    // todo refactor MultiWin
//    function Round.multiWinByOther(array $players) {
//        $playerArray = new ArrayList($players);
//        $playerArray->walk(function (Player $player) {
//            $this->assertPublicPhase($player);
//        });
//        // do
//        $winResults = $playerArray->toArray(function (Player $player) {
//            return $this->getWinResult($player);
//        });
//        $result = WinResult::createMultiWinByOther(
//            $this->getPlayerList()->toArray(), $players, $winResults, $this->getCurrentPlayer(),
//            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(),
//            $this->getRoundData()->getPrevailingWindData()->getSeatWindTurn());
//        // phase
//        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
//    }
//
//    function testMultiWinByOther() {
//        $r = new Round();
//        $r->debugDiscardByReplace($r->getAreas()->tempGetCurrentPlayer(), Tile::fromString('4s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[2], TileList::fromString('123m456m789m23s55s'));
//        $r->multiWinByOther([$r->getPlayerList()[1], $r->getPlayerList()[2]]);
//        $this->assertTrue($r->getRoundData()->getPhaseState()->getResult()->getResultType()->isWin());
//    }

    function testGameOver() {
        // to E Round N Dealer
        $r = new Round();
        $r->roll(false);
        $r->roll(false);
        $r->roll(false);
        // todo replace reset() by debugReset()

        // E Player winBySelf, but point not over 30000
        $r->getAreas()->debugSetPrivate($r->getAreas()->getCurrentSeatWind(), TileList::fromString('123m456m789m123s55s'), null, Tile::fromString('2m'));
        $r->getProcessor()->process('winBySelf E');
        $r->getAreas()->getCurrentArea()->setPoint('25000');
        $this->assertFalse($r->getPhaseState()->isGameOver($r));

        // point over 30000
        $r->getAreas()->getDealerArea()->setPoint('29999');

        $this->assertFalse($r->getPhaseState()->isGameOver($r));
        $r->getAreas()->getDealerArea()->setPoint('30000');
        $this->assertTrue($r->getPhaseState()->isGameOver($r));
    }
}

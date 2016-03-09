<?php

use Saki\Game\RoundPhase;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Game\Round;

class RoundWinTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        // setup
        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();
        // setup
        $r->getRoundData()->getTileAreas()->debugSetPrivate($r->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'));
        // execute
        $r->winBySelf($r->getCurrentPlayer());
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::OVER_PHASE), $r->getRoundPhase());
        // score changed
        $dealer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        foreach ($r->getPlayerList() as $player) {
            $scoreDelta = $r->getRoundData()->getPhaseState()->getRoundResult()->getScoreDelta($player);
            $deltaInt = $scoreDelta->getDeltaInt();
            if ($player == $dealer) {
                $this->assertGreaterThan(0, $deltaInt);
                $this->assertEquals($scoreDelta->getAfter(), $player->getScore(), $scoreDelta);
            } else {
                $this->assertLessThan(0, $deltaInt);
                $this->assertEquals($scoreDelta->getAfter(), $player->getScore(), $scoreDelta);
            }
        }
        // test toNextRound
        $this->assertEquals(RoundPhase::getOverInstance(), $r->getRoundPhase());
        $r->getRoundData()->toNextRound();
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getRoundPhase());
        // todo assert private state

        $this->assertEquals($dealer, $r->getRoundData()->getPlayerList()->getDealerPlayer());
        // todo test initial state
    }

    function testWinByOther() {
        $r = new Round();
        $pro = $r->getRoundData()->getProcessor();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
//        $r->winByOther($r->getPlayerList()[1]);
        $pro->process('winByOther S');
        $this->assertTrue($r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->isWin());
    }

    // todo refactor MultiWin
//    function Round.multiWinByOther(array $players) {
//        $playerArray = new ArrayLikeObject($players);
//        $playerArray->walk(function (Player $player) {
//            $this->assertPublicPhase($player);
//        });
//        // do
//        $winResults = $playerArray->toArray(function (Player $player) {
//            return $this->getWinResult($player);
//        });
//        $result = WinRoundResult::createMultiWinByOther(
//            $this->getPlayerList()->toArray(), $players, $winResults, $this->getCurrentPlayer(),
//            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(),
//            $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
//        // phase
//        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
//    }
//
//    function testMultiWinByOther() {
//        $r = new Round();
//        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[2], TileList::fromString('123m456m789m23s55s'));
//        $r->multiWinByOther([$r->getPlayerList()[1], $r->getPlayerList()[2]]);
//        $this->assertTrue($r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->isWin());
//    }

    function testGameOver() {
        // to E Round N Dealer
        $rd = new \Saki\Game\RoundData();
        $rd->reset(false);
        $rd->reset(false);
        $rd->reset(false);
        $r = new Round($rd);
        $this->assertSame($rd, $r->getRoundData());

        // E Player winBySelf, but score not over 30000
        $rd->getTileAreas()->debugSetPrivate($r->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'), null, Tile::fromString('2m'));
        $r->winBySelf($r->getCurrentPlayer());
        $r->getCurrentPlayer()->setScore('25000');
        $this->assertFalse($rd->getPhaseState()->isGameOver($rd));

        // score over 30000
        $dealerPlayer = $rd->getPlayerList()->getDealerPlayer();
        $dealerPlayer->setScore('29999');
        $this->assertFalse($rd->getPhaseState()->isGameOver($rd));
        $dealerPlayer->setScore('30000');
        $this->assertTrue($rd->getPhaseState()->isGameOver($rd));
    }
}

<?php

use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class RoundWinTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        // setup
        $r = new Round();
        $pro = $r->getProcessor();
        // setup
        $r->getTileAreas()->debugSetPrivate($r->getTurnManager()->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'));
        // execute
        $r->getProcessor()->process('winBySelf E');
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::OVER_PHASE), $r->getPhaseState()->getRoundPhase());
        // score changed
        $dealer = $r->getPlayerList()->getDealerPlayer();
        foreach ($r->getPlayerList() as $player) {
            $scoreDelta = $r->getPhaseState()->getRoundResult()->getScoreDelta($player);
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
        $this->assertEquals(RoundPhase::getOverInstance(), $r->getPhaseState()->getRoundPhase());
        $r->toNextRound();
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());
        // todo assert private state

        $this->assertEquals($dealer, $r->getPlayerList()->getDealerPlayer());
        // todo test initial state
    }

    function testWinByOther() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard E E:s-4s:4s; mockHand S 123m456m789m23s55s; winByOther S');
        $this->assertTrue($r->getPhaseState()->getRoundResult()->getRoundResultType()->isWin());
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
//        $r->debugDiscardByReplace($r->getTurnManager()->getCurrentPlayer(), Tile::fromString('4s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
//        $r->getRoundData()->getTileAreas()->debugSetPublic($r->getPlayerList()[2], TileList::fromString('123m456m789m23s55s'));
//        $r->multiWinByOther([$r->getPlayerList()[1], $r->getPlayerList()[2]]);
//        $this->assertTrue($r->getRoundData()->getPhaseState()->getRoundResult()->getRoundResultType()->isWin());
//    }

    function testGameOver() {
        // to E Round N Dealer
        $r = new Round();
        $r->reset(false);
        $r->reset(false);
        $r->reset(false);
        // todo replace reset() by debugReset()

        // E Player winBySelf, but score not over 30000
        $r->getTileAreas()->debugSetPrivate($r->getTurnManager()->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'), null, Tile::fromString('2m'));
        $r->getProcessor()->process('winBySelf E');
        $r->getTurnManager()->getCurrentPlayer()->setScore('25000');
        $this->assertFalse($r->getPhaseState()->isGameOver($r));

        // score over 30000
        $dealerPlayer = $r->getPlayerList()->getDealerPlayer();
        $dealerPlayer->setScore('29999');
        $this->assertFalse($r->getPhaseState()->isGameOver($r));
        $dealerPlayer->setScore('30000');
        $this->assertTrue($r->getPhaseState()->isGameOver($r));
    }
}

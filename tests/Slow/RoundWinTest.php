<?php

use Saki\Game\MockRound;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\RoundResult\WinRoundResult;
use Saki\Util\MsTimer;

class RoundWinTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        // setup
        $r = new MockRound();
        // setup
        $r->getRoundData()->getTileAreas()->debugSet($r->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'));
        // execute
        $r->winBySelf($r->getCurrentPlayer());
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::OVER_PHASE), $r->getRoundPhase());
        // score changed
        $dealer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        foreach ($r->getPlayerList() as $player) {
            $scoreDelta = $r->getRoundData()->getTurnManager()->getRoundResult()->getScoreDelta($player);
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
        $r->toNextRound();
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());
        $this->assertEquals($dealer, $r->getRoundData()->getPlayerList()->getDealerPlayer());
        // todo test initial state
    }

    function testWinByOther() {
        $r = new MockRound();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $r->debugSetHand($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
        $r->winByOther($r->getPlayerList()[1]);
        $this->assertTrue($r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->isWin());
    }

    function testMultiWinByOther() {
        $r = new MockRound();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $r->debugSetHand($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
        $r->debugSetHand($r->getPlayerList()[2], TileList::fromString('123m456m789m23s55s'));
        $r->multiWinByOther([$r->getPlayerList()[1], $r->getPlayerList()[2]]);
        $this->assertTrue($r->getRoundData()->getTurnManager()->getRoundResult()->getRoundResultType()->isWin());
    }

    function testGameOver() {
        // to E Round N Dealer
        $rd = new \Saki\Game\RoundData();
        $rd->reset(false);
        $rd->reset(false);
        $rd->reset(false);
        $r = new Round($rd);
        $this->assertSame($rd, $r->getRoundData());
        $this->assertFalse($r->isGameOver());

        // E Player winBySelf, but score not over 30000
        $r->getRoundData()->getTileAreas()->debugSet($r->getCurrentPlayer(), TileList::fromString('123m456m789m123s55s'), null, Tile::fromString('2m'));
        $r->winBySelf($r->getCurrentPlayer());
        $r->getCurrentPlayer()->setScore('25000');
        $this->assertFalse($r->isGameOver());

        // score over 30000
        $dealerPlayer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        $dealerPlayer->setScore('29999');
        $this->assertFalse($r->isGameOver());
        $dealerPlayer->setScore('30000');
        $this->assertTrue($r->isGameOver());
    }
}

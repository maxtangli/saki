<?php

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\Meld;

class RoundTest extends PHPUnit_Framework_TestCase {
    function testInit() {
        $r = new Round();

        // phase
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());
        // initial current player
        $dealerPlayer = $r->getPlayerList()->getDealerPlayer();
        $this->assertSame($dealerPlayer, $r->getTurnManager()->getCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $dealerPlayer->getTileArea()->getHand()->getPrivate());
        $this->assertTrue($dealerPlayer->getTileArea()->getHand()->getTarget()->exist());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            /** @var Player $player */
            $player = $player;

//            $expected = $player == $dealerPlayer ? 14 : 13;
//            $this->assertCount($expected, $onHandTileList, sprintf('%s %s', $player, count($onHandTileList)));
        }
    }

    function testRoundWindData() {
        $r = new Round();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $r->reset(false);
        }
        $this->assertEquals(4, $r->getRoundWindData()->getRoundWindTurn());
        $this->assertTrue($r->getRoundWindData()->isLastOrExtraRound());
        $this->assertFalse($r->getRoundWindData()->isFinalRound());
    }

    function testGetFinalScoreItems() {
        $r = new Round();
        $scores = [
            31100, 24400, 22300, 22200
        ];
        foreach ($r->getPlayerList() as $k => $player) {
            $player->setScore($scores[$k]);
        }

        $expected = [
            [1, 42],
            [2, 4],
            [3, -18],
            [4, -28],
        ];

        // todo should test on GameOver state
        $target = new FinalScoreStrategyTarget($r->getPlayerList());
        $items = $r->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
        foreach ($r->getPlayerList() as $k => $player) {
            $item = $items[$k];
            list($expectedRank, $expectedPoint) = [$expected[$k][0], $expected[$k][1]];
            $this->assertEquals($expectedRank, $item->getRank());
            $this->assertEquals($expectedPoint, $item->getFinalPoint());
        }
    }

    function testChow() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');

        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(1);

        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHand()->getPublic()->count();
        $pro->process('mockHand S 23m; chow S 2m 3m');

        // phase changed
        $this->assertEquals(RoundPhase::create(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());

        // tiles moved to created meld
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getDeclare()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getPrivate()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscard()->count());
    }

    function testPong() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');

        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(2);

        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHand()->getPublic()->count();
        $pro->process('mockHand W 11m123456789p13s; pong W');

        // phase changed
        $this->assertEquals(RoundPhase::create(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());

        // tiles moved to created meld
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getDeclare()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getPrivate()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscard()->count());

        // hand
        $handW = $r->getPlayerList()->getWestPlayer()->getTileArea()->getHand();
        $this->assertEquals('123456789p13s', $handW->getPrivate()->toFormatString(true));
        $this->assertEquals('3s', $handW->getTarget()->getTile());
        $this->assertEquals('123456789p1s', $handW->getPublic()->toFormatString(true));
    }

    function testDebugSkipTo() {
        $r = new Round();
        $playerE = $r->getTurnManager()->getCurrentPlayer();

        // phase not changed
        $r->debugSkipTo($playerE, null, null, null);
        $this->assertEquals($playerE, $r->getTurnManager()->getCurrentPlayer());
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());

        // to public phase
        // todo

        // to other player
        // todo
    }
}

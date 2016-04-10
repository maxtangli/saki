<?php

use Saki\FinalPoint\FinalPointStrategyTarget;
use Saki\Game\Phase;
use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Meld\Meld;

class RoundTest extends PHPUnit_Framework_TestCase {
    function testInit() {
        $r = new Round();

        // phase
        $this->assertEquals(Phase::getPrivateInstance(), $r->getPhaseState()->getPhase());
        // initial current player
        $dealerPlayer = $r->getPlayerList()->getDealerPlayer();
        $this->assertSame($dealerPlayer, $r->getAreas()->tempGetCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $dealerPlayer->getArea()->getHand()->getPrivate());
        $this->assertTrue($dealerPlayer->getArea()->getHand()->getTarget()->exist());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            /** @var Player $player */
            $player = $player;

//            $expected = $player == $dealerPlayer ? 14 : 13;
//            $this->assertCount($expected, $onHandTileList, sprintf('%s %s', $player, count($onHandTileList)));
        }
    }

    function testPrevailingWindData() {
        $r = new Round();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $r->reset(false);
        }
        $this->assertEquals(4, $r->getPrevailingWindData()->getPrevailingWindTurn());
        $this->assertTrue($r->getPrevailingWindData()->isLastOrExtraRound());
        $this->assertFalse($r->getPrevailingWindData()->isFinalRound());
    }

    function testGetFinalPointItems() {
        $r = new Round();
        $points = [
            31100, 24400, 22300, 22200
        ];
        foreach ($r->getPlayerList() as $k => $player) {
            /** @var Player $player */
            $player = $player;
            $player->getArea()->setPoint($points[$k]);
        }

        $expected = [
            [1, 42],
            [2, 4],
            [3, -18],
            [4, -28],
        ];

        // todo should test on GameOver state
        $target = new FinalPointStrategyTarget($r->getPlayerList());
        $items = $r->getGameData()->getFinalPointStrategy()->getFinalPointItems($target);
        foreach ($r->getPlayerList() as $k => $player) {
            $item = $items[$k];
            list($expectedRank, $expectedPoint) = [$expected[$k][0], $expected[$k][1]];
            $this->assertEquals($expectedRank, $item->getRank());
            $this->assertEquals($expectedPoint, $item->getFinalPointNumber());
        }
    }

    function testChow() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');

        // setup
        $prePlayer = $r->getAreas()->tempGetCurrentPlayer();
        $actPlayer = $r->getAreas()->tempGetOffsetPlayer(1);

        // execute
        $tileCountBefore = $actPlayer->getArea()->getHand()->getPublic()->count();
        $pro->process('mockHand S 23m; chow S 2m 3m');

        // phase changed
        $this->assertEquals(Phase::create(Phase::PRIVATE_PHASE), $r->getPhaseState()->getPhase());
        $this->assertEquals($actPlayer, $r->getAreas()->tempGetCurrentPlayer());

        // tiles moved to created meld
        $this->assertTrue($r->getAreas()->tempGetCurrentPlayer()->getArea()->getHand()->getDeclare()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getAreas()->tempGetCurrentPlayer()->getArea()->getHand()->getPrivate()->count());
        $this->assertEquals(0, $prePlayer->getArea()->getDiscard()->count());
    }

    function testPong() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');

        // setup
        $prePlayer = $r->getAreas()->tempGetCurrentPlayer();
        $actPlayer = $r->getAreas()->tempGetOffsetPlayer(2);

        // execute
        $tileCountBefore = $actPlayer->getArea()->getHand()->getPublic()->count();
        $pro->process('mockHand W 11m123456789p13s; pong W');

        // phase changed
        $this->assertEquals(Phase::create(Phase::PRIVATE_PHASE), $r->getPhaseState()->getPhase());
        $this->assertEquals($actPlayer, $r->getAreas()->tempGetCurrentPlayer());

        // tiles moved to created meld
        $this->assertTrue($r->getAreas()->tempGetCurrentPlayer()->getArea()->getHand()->getDeclare()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getAreas()->tempGetCurrentPlayer()->getArea()->getHand()->getPrivate()->count());
        $this->assertEquals(0, $prePlayer->getArea()->getDiscard()->count());

        // hand
        $handW = $r->getPlayerList()->getWestPlayer()->getArea()->getHand();
        $this->assertEquals('123456789p13s', $handW->getPrivate()->toFormatString(true));
        $this->assertEquals('3s', $handW->getTarget()->getTile());
        $this->assertEquals('123456789p1s', $handW->getPublic()->toFormatString(true));
    }

    function testDebugSkipTo() {
        $r = new Round();
        $playerE = $r->getAreas()->tempGetCurrentPlayer();

        // phase not changed
        $r->debugSkipTo($playerE, null, null, null);
        $this->assertEquals($playerE, $r->getAreas()->tempGetCurrentPlayer());
        $this->assertEquals(Phase::getPrivateInstance(), $r->getPhaseState()->getPhase());

        // to public phase
        // todo

        // to other player
        // todo
    }
}

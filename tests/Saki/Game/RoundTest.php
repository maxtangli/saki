<?php

use Saki\FinalPoint\FinalPointStrategyTarget;
use Saki\Game\Phase;
use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\Meld;

class RoundTest extends PHPUnit_Framework_TestCase {
    function testNew() {
        $r = new Round();

        // phase
        $this->assertEquals(Phase::createPrivate(), $r->getPhaseState()->getPhase());
        // todo
    }

    function testRoll() {
        // todo
    }

    function testGetFinalPointItems() {
        $r = new Round();
        $points = [
//            ['E', 31100],
//            ['S', 24400],
//            ['W', 22300],
//            ['N', 22200]
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
        $pro->process('mockHand S 23m; chow S 2m 3m');
        // todo
    }

    function testPong() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');
        $pro->process('mockHand W 11m123456789p13s; pong W');
        // todo
    }
}

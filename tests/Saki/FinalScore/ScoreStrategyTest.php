<?php

use Saki\Game\Player;
use Saki\Game\Round;

class PointStrategyTest extends PHPUnit_Framework_TestCase {
    function testRankingHorse() {
        $points = [30000, 10000, -10000, -30000];
        $s = new \Saki\FinalPoint\RankingHorseFinalPointStrategy($points);

        $set = [
            [10000, -30000],
            [20000, -10000],
            [30000, 10000],
            [40000, 30000],
        ];

        $playerList = (new Round())->getPlayerList();
//        $playerList = \Saki\Game\PlayerList::createStandard();
        foreach ($set as $k => list($point, $expectedPoint)) {
            /** @var Player $player */
            $player = $playerList[$k];
            $player->getArea()->setPoint($point);
        }

        $t = new \Saki\FinalPoint\FinalPointStrategyTarget($playerList);
        foreach ($set as $k => list($point, $expectedPoint)) {
            $this->assertEquals($expectedPoint, $s->getPointDelta($t, $playerList[$k]));
        }
    }

    function testMound() {
        $s = new \Saki\FinalPoint\MoundFinalPointStrategy(25000, 30000);

        $set = [
            [31100, 22000],
            [24400, -6000],
            [22300, -8000],
            [22200, -8000],
        ];
//        $playerList = \Saki\Game\PlayerList::createStandard();
        $playerList = (new Round())->getPlayerList();
        foreach ($set as $k => list($point, $expectedPoint)) {
            /** @var Player $player */
            $player = $playerList[$k];
            $player->getArea()->setPoint($point);
        }

        $t = new \Saki\FinalPoint\FinalPointStrategyTarget($playerList);
        foreach ($set as $k => list($point, $expectedPoint)) {
            $this->assertEquals($expectedPoint, $s->getPointDelta($t, $playerList[$k]));
        }
    }

    function testCompound() {
        $points = [30000, 10000, -10000, -30000];
        $s1 = new \Saki\FinalPoint\RankingHorseFinalPointStrategy($points);
        $s2 = new \Saki\FinalPoint\MoundFinalPointStrategy(25000, 30000);
        $s = new \Saki\FinalPoint\CompositeFinalPointStrategy([$s1, $s2]);

        $set = [
            [31100, 22000 + 30000],
            [24400, -6000 + 10000],
            [22300, -8000 + -10000],
            [22200, -8000 - 30000],
        ];
//        $playerList = \Saki\Game\PlayerList::createStandard();
        $playerList = (new Round())->getPlayerList();

        foreach ($set as $k => list($point, $expectedPoint)) {
            /** @var Player $player */
            $player = $playerList[$k];
            $player->getArea()->setPoint($point);
        }

        $t = new \Saki\FinalPoint\FinalPointStrategyTarget($playerList);
        foreach ($set as $k => list($point, $expectedPoint)) {
            $this->assertEquals($expectedPoint, $s->getPointDelta($t, $playerList[$k]));
        }
    }
}
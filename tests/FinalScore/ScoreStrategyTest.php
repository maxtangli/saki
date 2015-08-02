<?php

class ScoreStrategyTest extends PHPUnit_Framework_TestCase {
    function testRankingHorse() {
        $scores = [30000, 10000, -10000, -30000];
        $s = new \Saki\FinalScore\RankingHorseFinalScoreStrategy($scores);

        $set = [
            [10000, -30000],
            [20000, -10000],
            [30000, 10000],
            [40000, 30000],
        ];
        $playerList = \Saki\Game\PlayerList::createStandard();
        foreach ($set as $k => list($score, $expectedScore)) {
            $playerList[$k]->setScore($score);
        }

        $t = new \Saki\FinalScore\FinalScoreStrategyTarget($playerList);
        foreach ($set as $k => list($score, $expectedScore)) {
            $this->assertEquals($expectedScore, $s->getScoreDelta($t, $playerList[$k]));
        }
    }

    function testMound() {
        $s = new \Saki\FinalScore\MoundFinalScoreStrategy(25000, 30000);

        $set = [
            [31100, 22000],
            [24400, -6000],
            [22300, -8000],
            [22200, -8000],
        ];
        $playerList = \Saki\Game\PlayerList::createStandard();
        foreach ($set as $k => list($score, $expectedScore)) {
            $playerList[$k]->setScore($score);
        }

        $t = new \Saki\FinalScore\FinalScoreStrategyTarget($playerList);
        foreach ($set as $k => list($score, $expectedScore)) {
            $this->assertEquals($expectedScore, $s->getScoreDelta($t, $playerList[$k]));
        }
    }

    function testCompound() {
        $scores = [30000, 10000, -10000, -30000];
        $s1 = new \Saki\FinalScore\RankingHorseFinalScoreStrategy($scores);
        $s2 = new \Saki\FinalScore\MoundFinalScoreStrategy(25000, 30000);
        $s = new \Saki\FinalScore\CompositeFinalScoreStrategy([$s1, $s2]);

        $set = [
            [31100, 22000 + 30000],
            [24400, -6000 + 10000],
            [22300, -8000 + -10000],
            [22200, -8000 - 30000],
        ];
        $playerList = \Saki\Game\PlayerList::createStandard();
        foreach ($set as $k => list($score, $expectedScore)) {
            $playerList[$k]->setScore($score);
        }

        $t = new \Saki\FinalScore\FinalScoreStrategyTarget($playerList);
        foreach ($set as $k => list($score, $expectedScore)) {
            $this->assertEquals($expectedScore, $s->getScoreDelta($t, $playerList[$k]));
        }
    }
}
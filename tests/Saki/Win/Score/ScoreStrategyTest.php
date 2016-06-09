<?php

use Saki\Game\PlayerType;
use Saki\Game\PointSetting;
use Saki\Win\Point\PointList;
use Saki\Win\Score\CompositeScoreStrategy;
use Saki\Win\Score\OkaScoreStrategy;
use Saki\Win\Score\RankUmaScoreStrategy;
use Saki\Win\Score\RemoveInitialScoreStrategy;

class ScoreStrategyTest extends SakiTestCase {
    function testComposite() {
        $pointSetting = new PointSetting(PlayerType::create(4), 25000, 30000);
        $s = new CompositeScoreStrategy($pointSetting, [
            new RankUmaScoreStrategy($pointSetting),
            new OkaScoreStrategy($pointSetting)
        ]);

        $raw = PointList::fromPointMap([
            'E' => 31100,
            'S' => 24400,
            'W' => 22300,
            'N' => 22200,
        ]);

        $actual = $s->rawToFinal($raw);
        $expected = PointList::fromPointMap([
            'E' => 31100 + 20000 + 20000,
            'S' => 24400 + 10000,
            'W' => 22300 - 10000,
            'N' => 22200 - 20000,
        ]);
        $this->assertEquals($expected, $actual, sprintf('%s != %s', $expected, $actual));
    }
}
<?php

use Saki\Game\PlayerType;
use Saki\Game\PointSetting;
use Saki\Game\SeatWind;
use Saki\Win\Point\PointList;
use Saki\Win\Score\CompositeScoreStrategy;
use Saki\Win\Score\OkaScoreStrategy;
use Saki\Win\Score\RankUmaScoreStrategy;
use Saki\Win\Score\RemoveInitialScoreStrategy;

class ScoreStrategyTest extends PHPUnit_Framework_TestCase {
    function testComposite() {
        $pointSetting = new PointSetting(PlayerType::create(4), 25000, 30000);
        $s = new CompositeScoreStrategy($pointSetting, [
            new RankUmaScoreStrategy($pointSetting),
            new OkaScoreStrategy($pointSetting)
        ]);

        $raw = PointList::fromPointPairs([
            [SeatWind::createEast(), 31100],
            [SeatWind::createSouth(), 24400],
            [SeatWind::createWest(), 22300],
            [SeatWind::createNorth(), 22200],
        ]);
        $actual = $s->rawToFinal($raw);
        $expected = PointList::fromPointPairs([
            [SeatWind::createEast(), 31100 + 20000 + 20000],
            [SeatWind::createSouth(), 24400 + 10000],
            [SeatWind::createWest(), 22300 - 10000],
            [SeatWind::createNorth(), 22200 - 20000],
        ])->toOrderByRank();
        $this->assertEquals($expected, $actual, sprintf('%s != %s', $expected, $actual));
    }
}
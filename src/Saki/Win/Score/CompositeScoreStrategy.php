<?php

namespace Saki\Win\Score;

use Saki\Game\PointSetting;
use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Score
 */
class CompositeScoreStrategy extends ScoreStrategy {
    private $strategyList;

    /**
     * @param PointSetting $pointSetting
     * @param ScoreStrategy[] $strategies
     */
    function __construct(PointSetting $pointSetting, array $strategies) {
        // ignore validate same setting
        parent::__construct($pointSetting);
        $this->strategyList = new ArrayList($strategies);
    }

    //region impl
    function getPointDelta(int $rank) {
        $getPointDelta = function (ScoreStrategy $strategy) use ($rank) {
            return $strategy->getPointDelta($rank);
        };
        return $this->strategyList->getSum($getPointDelta);
    }
    //endregion
}
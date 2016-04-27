<?php
namespace Saki\Win\Score;

/**
 * @package Saki\Win\Score
 */
class OkaScoreStrategy extends ScoreStrategy {
    //region impl
    function getPointDelta(int $rank) {
        return $rank == 1 ? $this->getPointSetting()->getPointDiffTotal() : 0;
    }
    //endregion
}
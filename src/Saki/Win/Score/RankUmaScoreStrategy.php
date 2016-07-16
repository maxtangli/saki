<?php
namespace Saki\Win\Score;

/**
 * @package Saki\Win\Score
 */
class RankUmaScoreStrategy extends ScoreStrategy {
    private $m = [
        1 => 20000,
        2 => 10000,
        3 => -10000,
        4 => -20000,
    ];

    //region impl
    function getPointDelta(int $rank) {
        return $this->m[$rank];
    }
    //endregion
}
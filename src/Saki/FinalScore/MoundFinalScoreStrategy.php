<?php
namespace Saki\FinalScore;

/**
 * オカ
 * @package Saki\Score
 */
class MoundFinalScoreStrategy extends FinalScoreStrategy {
    private $initialScore;
    private $originScore;

    /**
     * @param int $initialScore e.x. 25000
     * @param int $originScore e.x. 30000
     */
    function __construct($initialScore, $originScore) {
        $this->initialScore = $initialScore;
        $this->originScore = $originScore;
    }

    function __toString() {
        return sprintf('mound %s-%s', $this->getInitialScore(), $this->getOriginScore());
    }

    function getInitialScore() {
        return $this->initialScore;
    }

    function getOriginScore() {
        return $this->originScore;
    }

    function getReturnScore($playerCount) {
        return ($this->getOriginScore() - $this->getInitialScore()) * $playerCount;
    }

    function getScoreDelta(FinalScoreStrategyTarget $target, $player) {
        $originScoreDelta = $target->getLastRoundScore($player) - $this->getOriginScore();
        $finalDelta = $originScoreDelta >= 0 ? ceil($originScoreDelta / 1000) * 1000 : floor($originScoreDelta / 1000) * 1000;
        if ($target->getLastRoundScoreRanking($player) == 1) {
            $finalDelta += $this->getReturnScore($target->getPlayerCount());
        }
        return intval($finalDelta);
    }
}
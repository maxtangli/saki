<?php
namespace Saki\FinalScore;

/**
 * 順位ウマ
 * @package Saki\Score
 */
class RankingHorseFinalScoreStrategy extends FinalScoreStrategy {

    static function fromType(RankingHorseType $type) {
        return new self($type->toHorseScores());
    }

    private $scores;

    /**
     * @param int[] $horseScores e.x. 30000,10000,-10000,-30000
     */
    function __construct(array $horseScores) {
        $this->scores = $horseScores;
    }

    function __toString() {
        $tokens = array_map(function ($score) {
            return $score >= 0 ? "+$score" : "-$score";
        }, $this->scores);
        return sprintf("ranking-horse[%s]", implode(',', $tokens));
    }

    function getScoreDelta(FinalScoreStrategyTarget $target, $player) {
        return $this->getFinalScoreByRanking($target->getLastRoundScoreRanking($player));
    }

    protected function getFinalScoreByRanking($ranking) {
        return $this->scores[$ranking - 1];
    }
}
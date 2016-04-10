<?php
namespace Saki\FinalPoint;

/**
 * 順位ウマ
 * @package Saki\Point
 */
class RankingHorseFinalPointStrategy extends FinalPointStrategy {
    static function fromType(RankingHorseType $type) {
        return new self($type->toHorsePoints());
    }

    private $points;

    /**
     * @param int[] $horsePoints e.x. 30000,10000,-10000,-30000
     */
    function __construct(array $horsePoints) {
        $this->points = $horsePoints;
    }

    function __toString() {
        $tokens = array_map(function ($point) {
            return $point >= 0 ? "+$point" : "-$point";
        }, $this->points);
        return sprintf("ranking-horse[%s]", implode(',', $tokens));
    }

    function getPointDelta(FinalPointStrategyTarget $target, $player) {
        return $this->getFinalPointByRanking($target->getLastRoundPointRanking($player));
    }

    protected function getFinalPointByRanking($ranking) {
        return $this->points[$ranking - 1];
    }
}
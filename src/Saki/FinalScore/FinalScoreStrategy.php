<?php

namespace Saki\FinalScore;

abstract class FinalScoreStrategy {
    abstract function getScoreDelta(FinalScoreStrategyTarget $target, $player);

    function getFinalScore(FinalScoreStrategyTarget $target, $player) {
        return $target->getLastRoundScore($player) + $this->getScoreDelta($target, $player);
    }

    function getFinalPoint(FinalScoreStrategyTarget $target, $player) {
        return $this->getScoreDelta($target, $player) / 1000;
    }

    /**
     * @param FinalScoreStrategyTarget $target
     * @param $player
     * @return FinalScoreItem
     */
    function getFinalScoreItem(FinalScoreStrategyTarget $target, $player) {
        return new FinalScoreItem(
            $target->getLastRoundScoreRanking($player),
            $this->getFinalScore($target, $player),
            $this->getFinalPoint($target, $player)
        );
    }

    /**
     * @param FinalScoreStrategyTarget $target
     * @return FinalScoreItem[]
     */
    function getFinalScoreItems(FinalScoreStrategyTarget $target) {
        return array_map(function ($player) use ($target) {
            return $this->getFinalScoreItem($target, $player);
        }, $target->getPlayers());
    }
}
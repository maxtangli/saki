<?php

namespace Saki\FinalPoint;

abstract class FinalPointStrategy {
    abstract function getPointDelta(FinalPointStrategyTarget $target, $player);

    function getFinalPoint(FinalPointStrategyTarget $target, $player) {
        return $target->getLastRoundPoint($player) + $this->getPointDelta($target, $player);
    }

    function getFinalPointNumber(FinalPointStrategyTarget $target, $player) {
        return $this->getPointDelta($target, $player) / 1000;
    }

    /**
     * @param FinalPointStrategyTarget $target
     * @param $player
     * @return FinalPointItem
     */
    function getFinalPointItem(FinalPointStrategyTarget $target, $player) {
        return new FinalPointItem(
            $target->getLastRoundPointRanking($player),
            $this->getFinalPoint($target, $player),
            $this->getFinalPointNumber($target, $player)
        );
    }

    /**
     * @param FinalPointStrategyTarget $target
     * @return FinalPointItem[]
     */
    function getFinalPointItems(FinalPointStrategyTarget $target) {
        return array_map(function ($player) use ($target) {
            return $this->getFinalPointItem($target, $player);
        }, $target->getPlayers());
    }
}
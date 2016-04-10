<?php
namespace Saki\FinalPoint;

/**
 * オカ
 * @package Saki\Point
 */
class MoundFinalPointStrategy extends FinalPointStrategy {
    private $initialPoint;
    private $originPoint;

    /**
     * @param int $initialPoint e.x. 25000
     * @param int $originPoint e.x. 30000
     */
    function __construct($initialPoint, $originPoint) {
        $this->initialPoint = $initialPoint;
        $this->originPoint = $originPoint;
    }

    function __toString() {
        return sprintf('mound %s-%s', $this->getInitialPoint(), $this->getOriginPoint());
    }

    function getInitialPoint() {
        return $this->initialPoint;
    }

    function getOriginPoint() {
        return $this->originPoint;
    }

    function getReturnPoint($playerCount) {
        return ($this->getOriginPoint() - $this->getInitialPoint()) * $playerCount;
    }

    function getPointDelta(FinalPointStrategyTarget $target, $player) {
        $originPointDelta = $target->getLastRoundPoint($player) - $this->getOriginPoint();
        $finalDelta = $originPointDelta >= 0 ? ceil($originPointDelta / 1000) * 1000 : floor($originPointDelta / 1000) * 1000;
        if ($target->getLastRoundPointRanking($player) == 1) {
            $finalDelta += $this->getReturnPoint($target->getPlayerCount());
        }
        return intval($finalDelta);
    }
}
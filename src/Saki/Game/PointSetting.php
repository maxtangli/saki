<?php
namespace Saki\Game;

use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class PointSetting implements Immutable {
    private $playerType;
    private $initialPoint;
    private $originPoint;

    /**
     * @param PlayerType $playerType
     * @param int $initialPoint
     * @param int $originPoint
     */
    function __construct(PlayerType $playerType, int $initialPoint, int $originPoint) {
        $this->playerType = $playerType;
        $this->initialPoint = $initialPoint;
        $this->originPoint = $originPoint;
    }

    /**
     * @return int[] e.g. ['E' => $initialPoint, ...]
     */
    function getInitialPointMap() {
        return $this->getPlayerType()->getSeatWindMap($this->getInitialPoint());
    }

    /**
     * @return PlayerType
     */
    function getPlayerType() {
        return $this->playerType;
    }

    /**
     * @return int
     */
    function getInitialPoint() {
        return $this->initialPoint;
    }

    /**
     * @return int
     */
    function getOriginPoint() {
        return $this->originPoint;
    }

    /**
     * @return int
     */
    function getPointDiffTotal() {
        return ($this->getOriginPoint() - $this->getInitialPoint())
        * $this->getPlayerType()->getValue();
    }
}
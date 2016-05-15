<?php
namespace Saki\Game;
use Saki\Tile\Tile;

/**
 * @package Saki\Game
 */
class TargetHolder {
    /** @var Target */
    private $target;

    function __construct() {
        $this->init();
    }

    function init() {
        $this->target = Target::createNull();
    }
    
    /**
     * own target?
     *         current other
     * private       Y     N
     * public        N     Y
     * @param SeatWind $seatWind
     * @return Target
     */
    function getTarget(SeatWind $seatWind) {
        $ownTarget = $this->target->exist()
            && $this->target->isOwner($seatWind);
        return $ownTarget ? $this->target : Target::createNull();
    }

    /**
     * @param Target $target
     */
    function setTarget(Target $target) {
        $this->target = $target;
    }

    /**
     * @param SeatWind $seatWind
     * @param Tile $newTargetTile
     */
    function replaceTarget(SeatWind $seatWind, Tile $newTargetTile) {
        $currentTarget = $this->getTarget($seatWind);
        if (!$currentTarget->exist()) {
            throw new \InvalidArgumentException();
        }
        $this->target = $this->target->toSetValue($newTargetTile);
    }
}
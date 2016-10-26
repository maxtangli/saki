<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;

/**
 * private-get-current get ?
 * private-set-current set ? draw/drawReplacement/fromPublic=>keep
 * private-get-other   NULL
 * private-set-other   ignore
 * public -get-current NULL
 * public -set-current ignore
 * public -get-other   get last-open
 * public -set-other   set last-open
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
     * @param Tile $tile
     */
    function replaceTargetTile(Tile $tile) {
        $this->target = $this->target->toSetValue($tile);
    }
}
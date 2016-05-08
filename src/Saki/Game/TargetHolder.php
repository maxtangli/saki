<?php
namespace Saki\Game;

/**
 * @package Saki\Game
 */
class TargetHolder {
    private $target;

    function __construct() {
        $this->init();
    }

    function init() {
        $this->target = Target::createNull();
    }
    
    function temp_getTarget() {
        return $this->target;
    }
    
    /**
     * @param SeatWind $seatWind
     * @return Target
     */
    function getTarget(SeatWind $seatWind) {
        $seeTarget = $this->target->exist()
            && $this->target->isOwner($seatWind);
        return $seeTarget ? $this->target : Target::createNull();
    }

    /**
     * @param Target $target
     */
    function setTarget(Target $target) {
        $this->target = $target;
    }
}
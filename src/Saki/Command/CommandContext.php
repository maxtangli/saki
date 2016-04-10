<?php
namespace Saki\Command;

use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * A context where Command can execute.
 * @package Saki\Command
 */
class CommandContext {
    private $round;
    private $bindActor;

    /**
     * @param Round $round
     */
    function __construct(Round $round) {
        $this->round = $round;
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    //region sugar methods
    function getCurrentArea() {
        return $this->getRound()->getAreas()->getCurrentArea();
    }
    //endregion

    //region Actor concerned
    /**
     * WARNING: assume no concurrency of Command processing.
     * @param SeatWind $seatWind
     */
    function bindActor(SeatWind $seatWind) {
        // todo safe check
        $this->bindActor = $seatWind;    
    }

    function unbindActor() {
        // todo safe check
        $this->bindActor = null;
    }

    /**
     * @return SeatWind
     */
    protected function getActor() {
        if ($this->bindActor === null) {
            throw new \BadMethodCallException();
        }
        return $this->bindActor;
    }
    
    function getActorArea() {
        return $this->getRound()->getAreas()->getArea($this->getActor());
    }
    
    function getActorHand() {
        return $this->getActorArea()->getHand();
    }
    //endregion
}
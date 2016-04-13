<?php
namespace Saki\Command;

use Saki\Game\Areas;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * A context where Command execute.
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

    //region Actor
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
    //endregion

    //region non-Actor sugar methods
    /**
     * @return Areas
     */
    function getAreas() {
        return $this->getRound()->getAreas();
    }

    function getTurn() {
        return $this->getAreas()->getTurn();
    }

    function getPhase() {
        return $this->getRound()->getPhaseState()->getPhase();
    }

    function getCurrentSeatWind() {
        return $this->getAreas()->getCurrentSeatWind();
    }

    function getCurrentArea() {
        return $this->getAreas()->getCurrentArea();
    }
    //endregion

    //region Actor sugar methods
    function isActorCurrent() {
        return $this->getAreas()->getCurrentSeatWind() == $this->getActor();
    }

    function getActorArea() {
        return $this->getRound()->getAreas()->getArea($this->getActor());
    }

    function getActorHand() {
        return $this->getActorArea()->getHand();
    }

    //endregion

    function tempGetCurrentPlayer() {
        return $this->getAreas()->getCurrentArea()->getPlayer();
    }
}
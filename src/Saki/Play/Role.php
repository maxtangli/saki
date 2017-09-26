<?php

namespace Saki\Play;

use Saki\Game\Relation;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Play
 */
class Role {
    private $round;
    private $initialSeatWind;
    private $isPlayer;

    /**
     * @param Round $round
     * @param SeatWind $initialSeatWind
     * @return Role
     */
    static function createPlayer(Round $round, SeatWind $initialSeatWind) {
        return new self($round, $initialSeatWind, true);
    }

    /**
     * @param Round $round
     * @param SeatWind $initialSeatWind
     * @return Role
     */
    static function createViewer(Round $round, SeatWind $initialSeatWind) {
        return new self($round, $initialSeatWind, false);
    }

    /**
     * @param Round $round
     * @param SeatWind $initialSeatWind
     * @param bool $isPlayer
     */
    protected function __construct(Round $round, SeatWind $initialSeatWind, bool $isPlayer) {
        $this->round = $round;
        $this->initialSeatWind = $initialSeatWind;
        $this->isPlayer = $isPlayer;
    }

    /**
     * @return string
     */
    function __toString() {
        $prefix = $this->isPlayer() ? 'player' : 'viewer';
        return $prefix . '-' . $this->getActor();
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return SeatWind
     */
    function getInitialSeatWind() {
        return $this->initialSeatWind;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->round->getAreaList()
            ->getAreaByInitial($this->initialSeatWind)
            ->getSeatWind();
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isActor(SeatWind $seatWind) {
        return $this->getActor()->isSame($seatWind);
    }

    /**
     * @param SeatWind $seatWind
     * @return Relation
     */
    function getRelation(SeatWind $seatWind) {
        return Relation::createByOther($seatWind, $this->getActor());
    }

    /**
     * @return boolean
     */
    function isPlayer() {
        return $this->isPlayer;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function mayViewHand(SeatWind $seatWind) {
        return $this->isPlayer() ? $this->isActor($seatWind) : true;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function mayExecute(SeatWind $seatWind) {
        return $this->isPlayer() ? $this->isActor($seatWind) : false;
    }

    /**
     * @return bool
     */
    function mayExecuteDebug() {
        return true;
    }
}

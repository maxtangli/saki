<?php
namespace Saki\Play;

use Saki\Game\SeatWind;

/**
 * @package Saki\Play
 */
class Role {
    private $viewer;
    private $isPlayer;

    /**
     * @param SeatWind $self
     * @return Role
     */
    static function createPlayer(SeatWind $self) {
        return new self($self, true);
    }

    /**
     * @param SeatWind $self
     * @return Role
     */
    static function createViewer(SeatWind $self) {
        return new self($self, false);
    }

    /**
     * @param SeatWind $self
     * @param bool $isPlayer
     */
    protected function __construct(SeatWind $self, bool $isPlayer) {
        $this->viewer = $self;
        $this->isPlayer = $isPlayer;
    }

    /**
     * @return string
     */
    function __toString() {
        $prefix = $this->isPlayer() ? 'player' : 'viewer';
        return $prefix . '-' . $this->getViewer();
    }

    /**
     * @return SeatWind
     */
    function getViewer() {
        return $this->viewer;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isViewer(SeatWind $seatWind) {
        return $this->getViewer() == $seatWind;
    }

    /**
     * @param SeatWind $seatWind
     * @return string
     */
    function getRelation(SeatWind $seatWind) {
        return $seatWind->toRelation($this->getViewer());
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
        return $this->isPlayer() ? $this->isViewer($seatWind) : true;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function mayExecute(SeatWind $seatWind) {
        return $this->isPlayer() ? $this->isViewer($seatWind) : false;
    }
}
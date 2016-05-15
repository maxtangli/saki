<?php
namespace Saki\Game;

/**
 * @package Saki\Game
 */
class RiichiHolder {
    // Immutable
    private $playerType;
    // variable
    private $riichiPoints;
    // round variable
    /** @var RiichiStatus[] */
    private $riichiStatus;

    /**
     * @param PlayerType $playerType
     */
    function __construct(PlayerType $playerType) {
        $this->playerType = $playerType;
        $this->init();
    }

    /**
     * @param bool $isWin
     */
    function roll(bool $isWin) {
        $this->riichiPoints = $isWin ? 0 : $this->riichiPoints;
        $this->riichiStatus = $this->playerType->getSeatWindMap(RiichiStatus::createNotRiichi());
    }
    
    function init() {
        $this->riichiPoints = 0;
        $this->riichiStatus = $this->playerType->getSeatWindMap(RiichiStatus::createNotRiichi());
    }

    /**
     * @return int
     */
    function getRiichiPoints() {
        return $this->riichiPoints;
    }

    /**
     * @param SeatWind $seatWind
     * @return RiichiStatus
     */
    function getRiichiStatus(SeatWind $seatWind) {
        return $this->riichiStatus[$seatWind->__toString()];
    }

    /**
     * @param SeatWind $seatWind
     * @param RiichiStatus $riichiStatus
     */
    function setRiichiStatus(SeatWind $seatWind, RiichiStatus $riichiStatus) {
        $this->riichiPoints += 1000;
        $this->riichiStatus[$seatWind->__toString()] = $riichiStatus;
    }
}
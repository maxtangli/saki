<?php
namespace Saki\Game;

/**
 * @package Saki\Game
 */
class TurnHolder {
    private $turn;
    private $openHistory;
    private $claimHistory;

    function __construct() {
        $this->turn = Turn::createFirst();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
    }

    function init() {
        $this->turn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
    }

    /**
     * @return Turn
     */
    function getTurn() {
        return $this->turn;
    }

    /**
     * @return OpenHistory
     */
    function getOpenHistory() {
        return $this->openHistory;
    }

    /**
     * @return ClaimHistory
     */
    function getClaimHistory() {
        return $this->claimHistory;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isFirstTurnAndNoClaim(SeatWind $seatWind) {
        return $this->isFirstCycleAndNoClaim()
            && $this->getTurn()->getSeatWind() == $seatWind;
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isBeforeFirstTurnAndNoClaim(SeatWind $seatWind) {
        return $this->isFirstCycleAndNoClaim()
            && $this->getTurn()->getSeatWind()->isBefore($seatWind);
    }

    /**
     * @return bool
     */
    function isFirstCycleAndNoClaim() {
        return $this->getTurn()->isFirstCircle()
            && !$this->getClaimHistory()->hasClaim();
    }

    /**
     * Roll to $seatWind.
     * - If $seatWind is not current, handle CircleCount update.
     * - Do nothing otherwise.
     * @param SeatWind $seatWind
     */
    function toSeatWind(SeatWind $seatWind) {
        $this->turn = $this->turn->toNextSeatWind($seatWind);
    }
}
<?php
namespace Saki\Game;

use Saki\Tile\TileList;

/**
 * A roundly reset area own by a player.
 * @package Saki\Game
 */
class Area {
    // immutable
    private $player;
    // shared variable
    private $areas;
    // game variable
    private $seatWind;
    // round variable
    private $handHolder;

    /**
     * @param Player $player
     * @param Areas $areas
     */
    function __construct(Player $player, Areas $areas) {
        $this->player = $player;
        $this->areas = $areas;
        $this->seatWind = $player->getInitialSeatWind();
        $this->handHolder = new HandHolder($areas->getTargetHolder(), $player->getInitialSeatWind());
    }

    /**
     * @param SeatWind $seatWind
     */
    function roll(SeatWind $seatWind) {
        $this->seatWind = $seatWind;
        $this->handHolder->init($seatWind);
    }

    /**
     * @param SeatWind $seatWind
     */
    function debugInit(SeatWind $seatWind) {
        $this->seatWind = $seatWind;
        $this->handHolder->init($seatWind);
    }

    /**
     * @return Player
     */
    function getPlayer() {
        return $this->player;
    }

    /**
     * @return Areas
     */
    function getAreas() {
        return $this->areas;
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return bool
     */
    function isCurrentSeatWind() {
        return $this->getSeatWind() == $this->getAreas()->getCurrentSeatWind();
    }

    /**
     * @return int
     */
    function getPoint() {
        return $this->getAreas()->getPointHolder()
            ->getPoint($this->getSeatWind());
    }

    /**
     * @return Hand
     */
    function getHand() {
        return $this->handHolder->getHand();
    }

    /**
     * @param Hand $hand
     */
    function setHand(Hand $hand) {
        $this->handHolder->setHand($hand);
    }

    /**
     * @return RiichiStatus
     */
    function getRiichiStatus() {
        return $this->getAreas()->getRiichiHolder()
            ->getRiichiStatus($this->getSeatWind());
    }

    /**
     * @return bool
     */
    function isFirstTurnWin() {
        $riichiStatus = $this->getRiichiStatus();
        $currentTurn = $this->getAreas()->getTurn();

        if (!$riichiStatus->isFirstTurn($currentTurn)) {
            return false;
        }

        $claimHistory = $this->getAreas()->getClaimHistory();
        $noDeclareSinceRiichi = !$claimHistory->hasClaim($riichiStatus->getRiichiTurn());
        return $noDeclareSinceRiichi;
    }

    /**
     * @return TileList
     */
    function getDiscard() {
        return $this->getAreas()->getOpenHistory()
            ->getSelfDiscard($this->getSeatWind());
    }
}
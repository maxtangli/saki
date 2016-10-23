<?php
namespace Saki\Game;

use Saki\Tile\TileList;

/**
 * @package Saki\Game
 */
class Area {
    // immutable
    private $initialSeatWind;
    // shared variable
    private $round;
    // game variable
    private $seatWind;
    // round variable
    private $handHolder;

    /**
     * @param SeatWind $initialSeatWind
     * @param Round $round
     */
    function __construct(SeatWind $initialSeatWind, Round $round) {
        $this->initialSeatWind = $initialSeatWind;
        $this->round = $round;
        $this->seatWind = $initialSeatWind;
        $this->handHolder = new HandHolder($round->getTargetHolder(), $initialSeatWind);
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('actor[%s],%s', $this->getSeatWind(), $this->getHand());
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
     * @return SeatWind
     */
    function getInitialSeatWind() {
        return $this->initialSeatWind;
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
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return bool
     */
    function isCurrentSeatWind() {
        return $this->getSeatWind() == $this->getRound()->getCurrentSeatWind();
    }

    /**
     * @return bool
     */
    function isPrivateActor() {
        return $this->getRound()->getPhase()->isPrivate()
        && $this->isCurrentSeatWind();
    }

    /**
     * @return bool
     */
    function isPublicActor() {
        return $this->getRound()->getPhase()->isPublic()
        && !$this->isCurrentSeatWind();
    }

    /**
     * @return bool
     */
    function isPublicNextActor() {
        return $this->isPublicActor() &&
        $this->getRound()->getCurrentSeatWind()->toNext() == $this->getSeatWind();
    }

    /**
     * @return bool
     */
    function isActor() {
        return $this->isPrivateActor() || $this->isPublicActor();
    }

    /**
     * @return int
     */
    function getPoint() {
        return $this->getRound()->getPointHolder()
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
        return $this->getRound()->getRiichiHolder()
            ->getRiichiStatus($this->getSeatWind());
    }

    /**
     * @return bool
     */
    function isFirstTurnWin() {
        $riichiStatus = $this->getRiichiStatus();
        $currentTurn = $this->getRound()->getTurn();

        if (!$riichiStatus->isFirstTurn($currentTurn)) {
            return false;
        }

        $claimHistory = $this->getRound()->getClaimHistory();
        $noDeclareSinceRiichi = !$claimHistory->hasClaim($riichiStatus->getRiichiTurn());
        return $noDeclareSinceRiichi;
    }

    /**
     * @return TileList
     */
    function getDiscard() {
        return $this->getRound()->getOpenHistory()
            ->getSelfDiscard($this->getSeatWind());
    }
}
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
    private $round;
    // game variable
    private $seatWind;
    // round variable
    private $handHolder;

    /**
     * @param Player $player
     * @param Round $round
     */
    function __construct(Player $player, Round $round) {
        $this->player = $player;
        $this->round = $round;
        $this->seatWind = $player->getInitialSeatWind();
        $this->handHolder = new HandHolder($round->getTargetHolder(), $player->getInitialSeatWind());
    }

    /**
     * @return string
     */
    function __toString() { // todo
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
     * @return Player
     */
    function getPlayer() {
        return $this->player;
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
    function isActor() {
        $phase = $this->getRound()->getPhase();
        $isCurrentSeatWind = $this->isCurrentSeatWind();
        return ($phase->isPrivate() && $isCurrentSeatWind)
        || ($phase->isPublic() && !$isCurrentSeatWind);
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
<?php
namespace Saki\Win;

use Saki\Game\PrevailingWind;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Meld\MeldList;

// todo move yaku specific logic into XXXYaku
class WinTarget {
    private $actor;
    private $round;

    function __construct(SeatWind $actor, Round $round) {
        $this->actor = $actor;
        $this->round = $round;

        if (!$round->getPhase()->isPrivateOrPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid phase, expect[private or public phase] but given[%s].', $round->getPhase())
            );
        }
        // todo validate hand count, target tile
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->actor, $this->round);
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->getRound()->getPrevailingCurrent()->getStatus()->getPrevailingWind();
    }

    // todo remove
    function getPrevailingWindTile() {
        return $this->getRound()->getPrevailingCurrent()->getStatus()->getPrevailingWind()->getWindTile();
    }

    function getTileSet() {
        return $this->getRound()->getGameData()->getTileSet();
    }

    // about current
    function getTurn() {
        return $this->getRound()->getTurn();
    }

    function getPhase() {
        return $this->getRound()->getPhase();
    }

    function isPrivatePhase() {
        return $this->getPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->getPhase()->isPublic();
    }

    function getRound() {
        return $this->round;
    }

    function getActor() {
        return $this->actor;
    }

    function getActArea() {
        return $this->getRound()->getArea($this->getActor());
    }

    function getOpenHistory() {
        return $this->getRound()->getOpenHistory();
    }

    function getWallRemainTileAmount() {
        return $this->getRound()->getWall()->getRemainTileCount();
    }

    function getHand() {
        return $this->getRound()->getArea($this->getActor())->getHand();
    }

    function getPublicHand() { // todo remove
        return $this->getRound()->getArea($this->getActor())->getHand()->getPublic();
    }

    function getPrivateHand() { // todo remove
        return $this->getRound()->getArea($this->getActor())->getHand()->getPrivate();
    }

    function getPrivateComplete() { // todo remove
        return $this->getRound()->getArea($this->getActor())->getHand()->getComplete();
    }

    function getDeclaredMeldList() { // todo remove
        return $this->getRound()->getArea($this->getActor())->getHand()->getMelded();
    }

    function isConcealed() { // todo remove
        return $this->getRound()->getArea($this->getActor())->getHand()->isConcealed();
    }

    function getTileOfTargetTile() { // todo remove
        return $this->getActArea()->getHand()->getTarget()->getTile();
    }

    function getDiscardedTileList() {
        return $this->getRound()->getArea($this->getActor())->getDiscardedReference();
    }

    function getRiichiStatus() {
        return $this->getRound()->getArea($this->getActor())->getRiichiStatus();
    }

    function isAfterAKong() {
        return $this->getActArea()->getHand()->getTarget()->isAfterAKong();
    }

    function isRobbingAKong() {
        return $this->getActArea()->getHand()->getTarget()->isRobbingAKong();
    }

    function isFirstTurnWin() {
        return $this->getActArea()->isFirstTurnWin();
    }

    function isBlessingOfHeaven() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->getPhase()->isPrivate()
        && $this->getActArea()->getSeatWind()->isDealer();
    }

    function isBlessingOfEarth() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->getPhase()->isPrivate()
        && $this->getActArea()->getSeatWind()->isLeisureFamily();
    }

    function isBlessingOfMan() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->getPhase()->isPublic()
        && $this->getActArea()->getDiscard()->isEmpty();
    }

    protected function isFirstTurnNoDeclare(SeatWind $seatWind) {
        $round = $this->getRound();
        return $round->getTurn()->isFirstCircle()
        && !$round->getClaimHistory()->hasClaim(
            new Turn(1, $seatWind)
        );
    }

    function getSeatWind() {
        return $this->getRound()->getArea($this->getActor())->getSeatWind();
    }

    // todo remove
    function getSeatWindTile() {
        return $this->getRound()->getArea($this->getActor())->getSeatWind()->getWindTile();
    }

    function getDoraFacade() {
        return $this->getRound()->getWall()->getDoraFacade();
    }
}
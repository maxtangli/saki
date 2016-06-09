<?php
namespace Saki\Win;

use Saki\Game\Areas;
use Saki\Game\PrevailingWind;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Meld\MeldList;

// todo move yaku specific logic into XXXYaku
class WinTarget {
    private $actor;
    private $areas;

    function __construct(SeatWind $actor, Areas $areas) {
        $this->actor = $actor;
        $this->areas = $areas;

        if (!$areas->getPhase()->isPrivateOrPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid phase, expect[private or public phase] but given[%s].', $areas->getPhase())
            );
        }
        // todo validate hand count, target tile
    }

    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($handMeldList, $this->actor, $this->areas);
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->getAreas()->getPrevailingCurrent()->getStatus()->getPrevailingWind();
    }

    // todo remove
    function getPrevailingWindTile() {
        return $this->getAreas()->getPrevailingCurrent()->getStatus()->getPrevailingWind()->getWindTile();
    }

    function getTileSet() {
        return $this->getAreas()->getGameData()->getTileSet();
    }

    // about areas/current
    function getTurn() {
        return $this->getAreas()->getTurn();
    }

    function getPhase() {
        return $this->getAreas()->getPhase();
    }

    function isPrivatePhase() {
        return $this->getPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->getPhase()->isPublic();
    }

    function getAreas() {
        return $this->areas;
    }

    function getActor() {
        return $this->actor;
    }

    function getActArea() {
        return $this->getAreas()->getArea($this->getActor());
    }

    function getOpenHistory() {
        return $this->getAreas()->getOpenHistory();
    }

    function getWallRemainTileAmount() {
        return $this->getAreas()->getWall()->getRemainTileCount();
    }

    function getHand() {
        return $this->getAreas()->getArea($this->getActor())->getHand();
    }

    function getPublicHand() { // todo remove
        return $this->getAreas()->getArea($this->getActor())->getHand()->getPublic();
    }

    function getPrivateHand() { // todo remove
        return $this->getAreas()->getArea($this->getActor())->getHand()->getPrivate();
    }

    function getPrivateComplete() { // todo remove
        return $this->getAreas()->getArea($this->getActor())->getHand()->getComplete();
    }

    function getDeclaredMeldList() { // todo remove
        return $this->getAreas()->getArea($this->getActor())->getHand()->getMelded();
    }

    function isConcealed() { // todo remove
        return $this->getAreas()->getArea($this->getActor())->getHand()->isConcealed();
    }

    function getTileOfTargetTile() { // todo remove
        return $this->getActArea()->getHand()->getTarget()->getTile();
    }

    function getDiscardedTileList() {
        return $this->getAreas()->getArea($this->getActor())->getDiscardedReference();
    }

    function getRiichiStatus() {
        return $this->getAreas()->getArea($this->getActor())->getRiichiStatus();
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
        $areas = $this->getAreas();
        return $areas->getTurn()->isFirstCircle()
        && !$areas->getClaimHistory()->hasClaim(
            new Turn(1, $seatWind)
        );
    }

    function getSeatWind() {
        return $this->getAreas()->getArea($this->getActor())->getSeatWind();
    }

    // todo remove
    function getSeatWindTile() {
        return $this->getAreas()->getArea($this->getActor())->getSeatWind()->getWindTile();
    }

    function getDoraFacade() {
        return $this->getAreas()->getWall()->getDoraFacade();
    }
}
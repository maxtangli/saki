<?php
namespace Saki\Win;

use Saki\Game\PrevailingWind;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Turn;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

// todo move yaku specific logic into XXXYaku
class WinTarget {
    private $actor;
    private $round;

    function __construct(SeatWind $actor, Round $round) {
        $this->actor = $actor;
        $this->round = $round;

        $phase = $round->getPhaseState()->getPhase();
        if (!$phase->isPrivateOrPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid round phase, expect[private or public phase] but given[%s].', $phase)
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
        return $this->round->getPrevailingCurrent()->getStatus()->getPrevailingWind();
    }

    // todo remove
    function getPrevailingWindTile() {
        return $this->round->getPrevailingCurrent()->getStatus()->getPrevailingWind()->getWindTile();
    }

    function getTileSet() {
        return $this->round->getGameData()->getTileSet();
    }

    // about round/current
    function getTurn() {
        return $this->round->getAreas()->getTurn();
    }

    function isPrivatePhase() {
        return $this->round->getPhaseState()->getPhase()->isPrivate();
    }

    function isPubicPhase() {
        return $this->round->getPhaseState()->getPhase()->isPublic();
    }

    function getAreas() {
        return $this->round->getAreas();
    }

    function getActor() {
        return $this->actor;
    }

    function getActArea() {
        return $this->getAreas()->getArea($this->getActor());
    }

    function getOpenHistory() {
        return $this->round->getAreas()->getOpenHistory();
    }

    function getOutsideRemainTileAmount(Tile $tile) {
        return $this->round->getAreas()->getOutsideRemainTileAmount($tile);
    }

    function getWallRemainTileAmount() {
        return $this->round->getAreas()->getWall()->getRemainTileCount();
    }

    function getPublicHand() {
        return $this->getAreas()->getArea($this->getActor())->getHand()->getPublic();
    }

    function getPrivateHand() {
        return $this->getAreas()->getArea($this->getActor())->getHand()->getPrivate();
    }

    function getPrivateComplete() {
        return $this->getAreas()->getArea($this->getActor())->getHand()->getPrivatePlusDeclare();
    }

    function getDeclaredMeldList() {
        return $this->getAreas()->getArea($this->getActor())->getHand()->getDeclare();
    }

    function isConcealed() {
        return $this->getAreas()->getArea($this->getActor())->getHand()->isConcealed();
    }

    function getTileOfTargetTile() {
        return $this->getActArea()->getHand()->getTarget()->getTile();
    }

    function getDiscardedTileList() {
        return $this->getAreas()->getArea($this->getActor())->getDiscardedReference();
    }

    function getReachStatus() {
        return $this->getAreas()->getArea($this->getActor())->getReachStatus();
    }

    function isKingSTileWin() {
        return $this->getActArea()->getHand()->getTarget()->isKingSTile();
    }

    function isRobbingAQuadWin() {
        return $this->getActArea()->getHand()->getTarget()->isRobQuadTile();
    }

    function isFirstTurnWin() {
        return $this->round->getAreas()->isFirstTurnWin($this->getActor());
    }

    function isHeavenlyWin() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->round->getPhaseState()->getPhase()->isPrivate()
        && $this->getActArea()->getSeatWind()->isDealer();
    }

    function isEarthlyWin() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->round->getPhaseState()->getPhase()->isPrivate()
        && $this->getActArea()->getSeatWind()->isLeisureFamily();
    }

    function isHumanlyWin() {
        return $this->isFirstTurnNoDeclare($this->getSeatWind())
        && $this->round->getPhaseState()->getPhase()->isPublic()
        && $this->getActArea()->getDiscard()->isEmpty();
    }

    protected function isFirstTurnNoDeclare(SeatWind $seatWind) {
        $r = $this->round;
        return $r->getAreas()->getTurn()->isFirstCircle()
        && !$r->getAreas()->getDeclareHistory()->hasDeclare(
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
        return $this->round->getAreas()->getWall()->getDoraFacade();
    }
}
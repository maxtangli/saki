<?php
namespace Saki\Win;

use Saki\Game\Area;
use Saki\Game\Hand;
use Saki\Game\Meld\MeldList;
use Saki\Game\OpenHistory;
use Saki\Game\Phase;
use Saki\Game\PrevailingWind;
use Saki\Game\RiichiStatus;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\Tile\TileList;
use Saki\Game\Wall;

/**
 * @package Saki\Win
 */
class WinTarget {
    private $round;
    private $actor;

    /**
     * @param SeatWind $actor
     * @param Round $round
     */
    function __construct(Round $round, SeatWind $actor) {
        $this->round = $round;
        $this->actor = $actor;

        if (!$this->getActorArea()->isPhaseActor()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid phase, expect[private or public phase] but given[%s].', $round->getPhase())
            );
        }
    }

    /**
     * @param MeldList $handMeldList
     * @return WinSubTarget
     */
    function toSubTarget(MeldList $handMeldList) {
        return new WinSubTarget($this->round, $this->actor, $handMeldList);
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
    function getActor() {
        return $this->actor;
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->getRound()->getPrevailing()
            ->getStatus()->getPrevailingWind();
    }

    /**
     * @return Phase
     */
    function getPhase() {
        return $this->getRound()->getPhase();
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->getRound()->getWall();
    }

    /**
     * @return OpenHistory
     */
    function getOpenHistory() {
        return $this->getRound()->getTurnHolder()->getOpenHistory();
    }

    /**
     * @return Area
     */
    function getActorArea() {
        return $this->getRound()->getArea($this->getActor());
    }

    /**
     * @return RiichiStatus
     */
    function getRiichiStatus() {
        return $this->getActorArea()->getRiichiStatus();
    }

    /**
     * @return Hand
     */
    function getHand() {
        return $this->getActorArea()->getHand();
    }

    /**
     * @return TileList
     */
    function getComplete() {
        return $this->getHand()->getComplete();
    }

    /**
     * @return Target
     */
    function getTarget() {
        return $this->getHand()->getTarget();
    }
}
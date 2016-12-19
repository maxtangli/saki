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
            $phase = $round->getPhase();
            throw new \InvalidArgumentException(
                "Invalid phase, expect[private or public phase] but given[$phase]."
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
     * @return Area
     */
    function getActorArea() {
        return $this->getRound()->getArea($this->getActor());
    }

    /**
     * Sugar method.
     * @return Hand
     */
    function getHand() {
        return $this->getActorArea()->getHand();
    }
}
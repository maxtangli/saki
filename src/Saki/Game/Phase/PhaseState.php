<?php
namespace Saki\Game\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

/**
 * @package Saki\Game\Phase
 */
abstract class PhaseState {
    private $round;
    private $customNextState;

    /**
     * @param Round $round
     */
    function __construct(Round $round) {
        $this->round = $round;
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return PhaseState|null
     */
    function getCustomNextState() {
        return $this->customNextState;
    }

    /**
     * @param PhaseState $customNextState
     */
    function setCustomNextState(PhaseState $customNextState) {
        $this->customNextState = $customNextState;
    }

    /**
     * @return PhaseState|PrivatePhaseState
     * @internal param Round $round
     */
    function getNextState() {
        return $this->getCustomNextState() ?? $this->getDefaultNextState();
    }

    /**
     * @return bool
     */
    function isRoundOver() {
        return $this->getPhase()->isOver();
    }

    /**
     * @return bool
     */
    function isGameOver() {
        return false;
    }

    /**
     * @return bool
     */
    function canToNextRound() {
        return $this->isRoundOver() && !$this->isGameOver();
    }

    //region subclass hooks
    /**
     * @return Phase
     */
    abstract function getPhase();

    /**
     * @return PhaseState
     */
    abstract function getDefaultNextState();

    /**
     * @return
     * @internal param Round $round
     */
    abstract function enter();

    /**
     * @return
     * @internal param Round $round
     */
    abstract function leave();
    //endregion
}
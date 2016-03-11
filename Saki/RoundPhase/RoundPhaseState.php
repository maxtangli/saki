<?php
namespace Saki\RoundPhase;

use Saki\Game\Round;
use Saki\Game\RoundPhase;

abstract class RoundPhaseState {
    private $customNextState;

    /**
     * @return RoundPhaseState|null
     */
    function getCustomNextState() {
        return $this->customNextState;
    }

    /**
     * @param RoundPhaseState $customNextState
     */
    function setCustomNextState(RoundPhaseState $customNextState) {
        $this->customNextState = $customNextState;
    }

    /**
     * @param Round $round
     * @return RoundPhaseState|PrivatePhaseState
     */
    function getNextState(Round $round) {
        return $this->getCustomNextState() ?? $this->getDefaultNextState($round);
    }

    /**
     * @return RoundPhase
     */
    abstract function getRoundPhase();

    /**
     * @return RoundPhaseState
     */
    abstract function getDefaultNextState(Round $round);

    /**
     * @param Round $round
     */
    abstract function enter(Round $round);

    /**
     * @param Round $round
     */
    abstract function leave(Round $round);
}
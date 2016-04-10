<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

abstract class PhaseState {
    private $customNextState;

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
     * @param Round $round
     * @return PhaseState|PrivatePhaseState
     */
    function getNextState(Round $round) {
        return $this->getCustomNextState() ?? $this->getDefaultNextState($round);
    }

    /**
     * @return Phase
     */
    abstract function getPhase();

    /**
     * @return PhaseState
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
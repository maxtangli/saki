<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

/**
 * @package Saki\Phase
 */
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

    //region subclass hooks
    /**
     * @return Phase
     */
    abstract function getPhase();

    /**
     * @param Round $round
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
    //endregion
}
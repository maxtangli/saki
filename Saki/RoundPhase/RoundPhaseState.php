<?php
namespace Saki\RoundPhase;

use Saki\Game\RoundData;
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
     * @param RoundData $roundData
     * @return RoundPhaseState|PrivatePhaseState
     */
    function getNextState(RoundData $roundData) {
        return $this->getCustomNextState() ?? $this->getDefaultNextState($roundData);
    }

    /**
     * @return RoundPhase
     */
    abstract function getRoundPhase();

    /**
     * @return RoundPhaseState
     */
    abstract function getDefaultNextState(RoundData $roundData);

    /**
     * @param RoundData $roundData
     */
    abstract function enter(RoundData $roundData);

    /**
     * @param RoundData $roundData
     */
    abstract function leave(RoundData $roundData);
}
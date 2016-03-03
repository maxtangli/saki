<?php
namespace Saki\RoundPhase;

use Saki\Game\RoundData;

abstract class RoundPhaseState {
    private $customNextState;

    /**
     * @return RoundPhaseState|null
     */
    function getCustomNextState() {
        return $this->customNextState;
    }

    function setCustomNextState($customNextState) {
        $this->customNextState = $customNextState;
    }

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

    abstract function enter(RoundData $roundData);

    abstract function leave(RoundData $roundData);
}
<?php
namespace Saki\RoundPhase;

use Saki\Game\RoundData;
use Saki\Game\RoundPhase;

class NullPhaseState extends RoundPhaseState {
    function getRoundPhase() {
        return RoundPhase::getNullInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        return new InitPhaseState();
    }

    function enter(RoundData $roundData) {
        // todo
    }

    function leave(RoundData $roundData) {
        // do nothing
    }

}
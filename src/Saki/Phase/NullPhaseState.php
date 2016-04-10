<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

class NullPhaseState extends PhaseState {
    function getPhase() {
        return Phase::getNullInstance();
    }

    function getDefaultNextState(Round $round) {
        return new InitPhaseState();
    }

    function enter(Round $round) {
        // todo do what? write detailed next time
    }

    function leave(Round $round) {
        // do nothing
    }
}
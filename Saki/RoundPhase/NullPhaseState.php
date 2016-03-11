<?php
namespace Saki\RoundPhase;

use Saki\Game\Round;
use Saki\Game\RoundPhase;

class NullPhaseState extends RoundPhaseState {
    function getRoundPhase() {
        return RoundPhase::getNullInstance();
    }

    function getDefaultNextState(Round $round) {
        return new InitPhaseState();
    }

    function enter(Round $round) {
        // todo
    }

    function leave(Round $round) {
        // do nothing
    }

}
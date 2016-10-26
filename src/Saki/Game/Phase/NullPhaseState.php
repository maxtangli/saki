<?php
namespace Saki\Game\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

/**
 * @package Saki\Game\Phase
 */
class NullPhaseState extends PhaseState {
    //region PhaseState impl
    function getPhase() {
        return Phase::createNull();
    }

    function getDefaultNextState(Round $round) {
        return new InitPhaseState();
    }

    function enter(Round $round) {
        // do nothing
    }

    function leave(Round $round) {
        // do nothing
    }
    //endregion
}
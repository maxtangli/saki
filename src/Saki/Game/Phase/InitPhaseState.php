<?php
namespace Saki\Game\Phase;

use Saki\Game\Phase;
use Saki\Game\SeatWind;

/**
 * @package Saki\Game\Phase
 */
class InitPhaseState extends PhaseState {
    //region PhaseState impl
    function getPhase() {
        return Phase::createInit();
    }

    function getDefaultNextState() {
        return new PrivatePhaseState($this->getRound(), SeatWind::createEast(), true);
    }

    function enter() {
        // do nothing
    }

    function leave() {
        // do nothing
    }
    //endregion
}
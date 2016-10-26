<?php
namespace Saki\Game\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Game\Phase
 */
class InitPhaseState extends PhaseState {
    //region PhaseState impl
    function getPhase() {
        return Phase::createInit();
    }

    function getDefaultNextState(Round $round) {
        $nextActor = SeatWind::createEast();
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextActor, $shouldDrawTile);
    }

    function enter(Round $round) {
        $round->deal();
    }

    function leave(Round $round) {
        // do nothing
    }
    //endregion
}
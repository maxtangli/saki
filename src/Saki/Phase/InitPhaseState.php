<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Phase
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
        // todo move shuffle logic into here
        $round->deal();
    }

    function leave(Round $round) {
        // do nothing
    }
    //endregion
}
<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

class InitPhaseState extends PhaseState {
    function getPhase() {
        return Phase::getInitInstance();
    }

    function getDefaultNextState(Round $round) {
        $nextPlayer = $round->getPlayerList()->getDealerPlayer();
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextPlayer, $shouldDrawTile, true);
    }

    function enter(Round $round) {
        $round->getAreas()->drawInitForAll();
    }

    function leave(Round $round) {
        // do nothing
    }
}
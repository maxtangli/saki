<?php
namespace Saki\RoundPhase;

use Saki\Game\Round;
use Saki\Game\RoundPhase;

class InitPhaseState extends RoundPhaseState {
    function getRoundPhase() {
        return RoundPhase::getInitInstance();
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
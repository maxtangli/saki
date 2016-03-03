<?php
namespace Saki\RoundPhase;

use Saki\Game\RoundPhase;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\Game\RoundData;

class InitPhaseState extends RoundPhaseState {
    function getRoundPhase() {
        return RoundPhase::getInitInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        $nextPlayer = $roundData->getPlayerList()->getDealerPlayer();
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextPlayer, $shouldDrawTile, true);
    }

    function enter(RoundData $roundData) {
        // each player draw initial tiles
        $roundData->getTileAreas()->drawInitForAll();
        // go to dealer player's private phase todo right?
//        $roundData->getTurnManager()->start();
    }

    function leave(RoundData $roundData) {
        // do nothing
    }
}
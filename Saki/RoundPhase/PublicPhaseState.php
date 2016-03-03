<?php
namespace Saki\RoundPhase;

use Saki\Game\RoundData;
use Saki\Game\RoundPhase;

class PublicPhaseState extends RoundPhaseState {
    function getRoundPhase() {
        return RoundPhase::getPublicInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        $nextPlayer = $roundData->getTurnManager()->getOffsetPlayer(1);
        $shouldDrawTile = true;
        return new \Saki\RoundPhase\PrivatePhaseState($nextPlayer, $shouldDrawTile);
    }

    function enter(RoundData $roundData) {
        // do nothing
    }

    function leave(RoundData $roundData) {
        // todo inRound draw
    }
}
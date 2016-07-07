<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class ToNextRoundCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    //region Command impl
    protected function executableImpl(Round $round) {
        $phaseState = $round->getPhaseState();
        return $phaseState->getPhase()->isOver()
            && !$phaseState->isGameOver($round);
    }

    protected function executeImpl(Round $round) {
        $round->toNextRound();
    }
    //endregion
}
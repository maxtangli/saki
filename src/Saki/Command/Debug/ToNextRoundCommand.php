<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class ToNextRoundCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    //region Command impl
    protected function executableImpl(Round $round) {
        return $round->getPhase()->isOver()
            && !$round->getPhaseState()->isGameOver($round);
    }

    protected function executeImpl(Round $round) {
        $round->toNextRound();
    }
    //endregion
}
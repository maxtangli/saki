<?php
namespace Saki\Command\DebugCommand;

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
        return $round->getPhaseState()->canToNextRound();
    }

    protected function executeImpl(Round $round) {
        $round->getPhaseState()->toNextRound();
    }
    //endregion
}
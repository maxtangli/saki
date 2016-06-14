<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class PassAllCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    //region Command impl
    protected function executableImpl(Round $round) {
        $phaseState = $round->getPhaseState();
        return $phaseState->getPhase()->isPublic();
    }

    protected function executeImpl(Round $round) {
        $round->toNextPhase();
    }
    //endregion
}
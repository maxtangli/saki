<?php

namespace Saki\Command\DebugCommand;

use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class ToGameOverCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    //region Command impl
    protected function executableImpl(Round $round) {
        return true;
    }

    protected function executeImpl(Round $round) {
        $round->debugToGameOver();
    }
    //endregion
}
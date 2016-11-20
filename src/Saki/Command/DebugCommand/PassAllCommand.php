<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\Command;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class PassAllCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    //region Command impl
    protected function executableImpl(Round $round) {
        return $round->getPhase()->isPublic();
    }

    protected function executeImpl(Round $round) {
        $round->toNextPhase();
    }
    //endregion
}
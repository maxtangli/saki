<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class InitCommand extends Command {
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
        $round->debugInit(PrevailingStatus::createFirst());
    }
    //endregion
}
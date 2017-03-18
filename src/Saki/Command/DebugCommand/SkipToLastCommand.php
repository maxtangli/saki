<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class SkipToLastCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    /**
     * @return int
     */
    function getSkipCount() {
        return $this->getParam(0);
    }
    
    //region Command impl
    protected function executableImpl(Round $round) {
        return $round->getPhase()->isPrivateOrPublic();
    }

    protected function executeImpl(Round $round) {
        $skipCount = $round->getWall()->getDrawWall()->getRemainTileCount();
        $round->process("skip $skipCount");
    }
    //endregion
}
<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Game\Round;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\Debug
 */
class MockWallRemainCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [IntParamDeclaration::class];
    }
    //endregion

    /**
     * @return int
     */
    function getWallRemainTileCount() {
        return $this->getParam(0);
    }

    /**
     * @return \Saki\Game\Wall
     */
    protected function getWall() {
        return $this->getRound()->getWall();
        
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        return $this->getWall()->getLiveWall()->getRemainTileCount()
        >= $this->getWallRemainTileCount();
    }

    protected function executeImpl(Round $round) {
        $this->getWall()->getLiveWall()->debugSetRemainTileCount($this->getWallRemainTileCount());
    }
    //endregion
}
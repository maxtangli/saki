<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Round;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\Debug
 */
class MockNextDrawCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [TileParamDeclaration::class];
    }
    //endregion

    /**
     * @return Tile
     */
    function getMockTile() {
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
        return $this->getWall()->getRemainTileCount() > 0;
    }

    protected function executeImpl(Round $round) {
        $this->getWall()->debugSetNextDrawTile($this->getMockTile());
    }
    //endregion
}
<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Round;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\Debug
 */
class MockNextReplaceCommand extends DebugCommand {
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
     * @return \Saki\Game\Wall\LiveWall
     */
    protected function getReplaceWall() {
        return $this->getRound()
            ->getWall()->getReplaceWall();
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        return $this->getReplaceWall()->ableOutNext();
    }

    protected function executeImpl(Round $round) {
        $this->getReplaceWall()->debugSetNextTile($this->getMockTile());
    }
    //endregion
}
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
     * @return \Saki\Game\Wall\DeadWall
     */
    protected function getDeadWall() {
        return $this->getRound()
            ->getWall()->getDeadWall();
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        return $this->getDeadWall()->isAbleDrawReplacement();
    }

    protected function executeImpl(Round $round) {
        $this->getDeadWall()->debugSetNextReplacement($this->getMockTile());
    }
    //endregion
}
<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Round;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\Debug
 */
class MockNextReplaceCommand extends Command {
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
     * @return \Saki\Game\DeadWall
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
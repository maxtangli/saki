<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\BoolParamDeclaration;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Game\Round;
use Saki\Game\Tile\TileList;

/**
 * @package Saki\Command\Debug
 */
class MockDeadWallCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [TileListParamDeclaration::class, IntParamDeclaration::class, BoolParamDeclaration::class];
    }
    //endregion

    /**
     * @return TileList
     */
    function getTileList() {
        return $this->getParam(0);
    }

    /**
     * @return int
     */
    function getOpenedIndicatorCount() {
        return $this->getParam(1);
    }

    /**
     * @return bool
     */
    function getUraDoraOpened() {
        return $this->getParam(2);
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
        return $this->getTileList()->count() == 10;
    }

    protected function executeImpl(Round $round) {
        $this->getDeadWall()->reset(
            $this->getTileList(), $this->getOpenedIndicatorCount(), $this->getUraDoraOpened()
        );
    }
    //endregion
}
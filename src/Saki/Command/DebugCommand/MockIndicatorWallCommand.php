<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\BoolParamDeclaration;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Game\Round;
use Saki\Game\Tile\TileList;
use Saki\Game\Wall\StackList;

/**
 * @package Saki\Command\Debug
 */
class MockIndicatorWallCommand extends DebugCommand {
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

    //region Command impl
    protected function executableImpl(Round $round) {
        return $this->getTileList()->count() == 10;
    }

    protected function executeImpl(Round $round) {
        $indicatorWall = $this->getRound()->getWall()->getIndicatorWall();
        $stackList = StackList::fromTileList($this->getTileList());
        $indicatorWall->reset(
            $stackList,
            $this->getOpenedIndicatorCount(),
            $this->getUraDoraOpened()
        );
    }
    //endregion
}
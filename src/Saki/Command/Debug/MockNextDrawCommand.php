<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Round;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\Debug
 */
class MockNextDrawCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [TileParamDeclaration::class];
    }
    //endregion

    /**
     * @param Round $round
     * @param Tile $mockTile
     */
    function __construct(Round $round, Tile $mockTile) {
        parent::__construct($round, [$mockTile]);
    }

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
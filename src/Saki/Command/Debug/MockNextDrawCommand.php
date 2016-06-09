<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
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
     * @param CommandContext $context
     * @param Tile $mockTile
     */
    function __construct(CommandContext $context, Tile $mockTile) {
        parent::__construct($context, [$mockTile]);
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
        return $this->getContext()->getRound()->getAreas()->getWall();
    }

    //region Command impl
    protected function executableImpl(CommandContext $context) {
        return $this->getWall()->getRemainTileCount() > 0;
    }

    protected function executeImpl(CommandContext $context) {
        $this->getWall()->debugSetNextDrawTile($this->getMockTile());
    }
    //endregion
}
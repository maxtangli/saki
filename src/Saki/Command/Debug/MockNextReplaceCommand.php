<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
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
     * @return \Saki\Game\DeadWall
     */
    protected function getDeadWall() {
        return $this->getContext()->getRound()->getAreas()
            ->getWall()->getDeadWall();
    }

    //region Command impl
    protected function executableImpl(CommandContext $context) {
        return $this->getDeadWall()->canDrawReplacement();
    }

    protected function executeImpl(CommandContext $context) {
        $this->getDeadWall()->debugSetNextDrawReplacement($this->getMockTile());
    }
    //endregion
}
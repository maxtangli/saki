<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Tile\Tile;

class MockWallCommand extends Command {
    static function getParamDeclarations() {
        return [TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $mockTile) {
        parent::__construct($context, [$mockTile]);
    }

    function getMockTile() {
        return $this->getParam(0);
    }

    function executable() {
        $wall = $this->getContext()->getRoundData()->getTileAreas()->getWall();
        return $wall->getRemainTileCount() > 0;
    }

    function executeImpl() {
        $wall = $this->getContext()->getRoundData()->getTileAreas()->getWall();
        $wall->debugSetNextDrawTile($this->getMockTile());
    }
}
<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Tile\Tile;

class MockNextDrawCommand extends Command {
    static function getParamDeclarations() {
        return [TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $mockTile) {
        parent::__construct($context, [$mockTile]);
    }

    function getMockTile() {
        return $this->getParam(0);
    }

    protected function executableImpl(CommandContext $context) {
        $wall = $this->getContext()->getRound()->getAreas()->getWall();
        return $wall->getRemainTileCount() > 0;
    }

    protected function executeImpl(CommandContext $context) {
        $wall = $this->getContext()->getRound()->getAreas()->getWall();
        $wall->debugSetNextDrawTile($this->getMockTile());
    }
}
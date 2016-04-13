<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Tile\Tile;

class MockNextReplaceCommand extends Command {
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
        // todo deadWall logic
        return true;
    }

    protected function executeImpl(CommandContext $context) {
        $wall = $this->getContext()->getRound()->getAreas()->getWall();
        $wall->debugSetNextReplaceTile($this->getMockTile());
    }
}
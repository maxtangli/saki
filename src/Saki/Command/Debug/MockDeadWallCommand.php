<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\BoolParamDeclaration;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Tile\TileList;

class MockDeadWallCommand extends Command {
    static function getParamDeclarations() {
        return [TileListParamDeclaration::class, IntParamDeclaration::class, BoolParamDeclaration::class];
    }

    function __construct(CommandContext $context,
                         TileList $tileList,
                         int $openedDoraCount,
                         bool $uraDoraOpened
    ) {
        parent::__construct($context, [$tileList, $openedDoraCount, $uraDoraOpened]);
    }

    /**
     * @return TileList
     */
    function getTileList() {
        return $this->getParam(0);
    }

    /**
     * @return int
     */
    function getOpenedDoraIndicatorCount() {
        return $this->getParam(1);
    }

    /**
     * @return bool
     */
    function getUraDoraOpened() {
        return $this->getParam(2);
    }

    protected function executableImpl(CommandContext $context) {
        return true;
    }

    protected function executeImpl(CommandContext $context) {
        $deadWall = $this->getContext()->getRound()->getAreas()->getWall()->getDeadWall();
        $deadWall->reset($this->getTileList(), $this->getOpenedDoraIndicatorCount(), $this->getUraDoraOpened());
    }
}
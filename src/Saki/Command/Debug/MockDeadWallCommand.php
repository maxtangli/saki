<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\BoolParamDeclaration;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Tile\TileList;

/**
 * @package Saki\Command\Debug
 */
class MockDeadWallCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [TileListParamDeclaration::class, IntParamDeclaration::class, BoolParamDeclaration::class];
    }
    //endregion

    /**
     * @param CommandContext $context
     * @param TileList $tileList
     * @param int $openedDoraIndicatorCount
     * @param bool $uraDoraOpened
     */
    function __construct(CommandContext $context,
                         TileList $tileList, int $openedDoraIndicatorCount, bool $uraDoraOpened) {
        parent::__construct($context, [$tileList, $openedDoraIndicatorCount, $uraDoraOpened]);
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

    /**
     * @return \Saki\Game\DeadWall
     */
    protected function getDeadWall() {
        return $this->getContext()->getRound()->getAreas()
            ->getWall()->getDeadWall();
    }

    //region Command impl
    protected function executableImpl(CommandContext $context) {
        return $this->getTileList()->count() == $this->getDeadWall()->getRemainTileCount();
    }

    protected function executeImpl(CommandContext $context) {
        $this->getDeadWall()->reset(
            $this->getTileList(), $this->getOpenedDoraIndicatorCount(), $this->getUraDoraOpened()
        );
    }
    //endregion
}
<?php
namespace Saki\Command\Debug;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Game\Area;
use Saki\Game\SeatWind;
use Saki\Tile\TileList;

/**
 * @package Saki\Command\Debug
 */
class MockHandCommand extends PlayerCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }
    //endregion

    /**
     * @param CommandContext $context
     * @param SeatWind $seatWind
     * @param TileList $mockTileList
     */
    function __construct(CommandContext $context, SeatWind $seatWind, TileList $mockTileList) {
        parent::__construct($context, [$seatWind, $mockTileList]);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    //region PlayerCommand impl
    protected function matchPhase(CommandContext $context, Area $actorArea) {
        $phaseState = $context->getRound()->getAreas()->getPhaseState();
        return $phaseState->getPhase()->isPrivateOrPublic();
    }

    protected function matchActor(CommandContext $context, Area $actorArea) {
        return true;
    }

    protected function matchOther(CommandContext $context, Area $actorArea) {
        $mockTileList = $this->getMockTileList();
        $hand = $actorArea->getHand();
        return ($mockTileList->count() <= $hand->getPublic()->count())
        || ($hand->isComplete() && $mockTileList->count() <= $hand->getPrivate()->count());
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $actorArea->setHand($actorArea->getHand()->toMockHand($this->getMockTileList()));
    }
    //endregion
}
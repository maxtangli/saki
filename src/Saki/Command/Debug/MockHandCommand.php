<?php
namespace Saki\Command\Debug;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Game\SeatWind;
use Saki\Tile\TileList;

class MockHandCommand extends PlayerCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $seatWind, TileList $mockTileList) {
        parent::__construct($context, [$seatWind, $mockTileList]);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    protected function matchPhase(CommandContext $context) {
        return $context->getPhase()->isPrivateOrPublic();
    }

    protected function matchActor(CommandContext $context) {
        return true;
    }

    protected function matchOther(CommandContext $context) {
        $context = $this->getContext();
        $mockTileList = $this->getMockTileList();

        $hand = $context->getActorHand();
        if ($mockTileList->count() <= $hand->getPublic()->count()) {
            return true;
        }

        if ($hand->isComplete()
            && $mockTileList->count() <= $hand->getPrivate()->count()
        ) {
            return true;
        }

        return false;
    }

    protected function executeImpl(CommandContext $context) {
        $context->getActorArea()->debugMockHand($this->getMockTileList());
    }
}
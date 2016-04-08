<?php
namespace Saki\Command\Debug;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockHandCommand extends PlayerCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $selfWind, TileList $mockTileList) {
        parent::__construct($context, [$selfWind, $mockTileList]);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    function matchRequiredPhases() {
        return $this->getRoundPhase()->isPrivateOrPublic();
    }

    function matchRequiredPlayer() {
        return true;
    }

    function matchOtherConditions() {
        $mockTileList = $this->getMockTileList();

        $hand = $this->getActPlayer()->getTileArea()->getHand();
        if ($mockTileList->count() <= $hand->getPublic()->count()) {
            return true;
        }

        if ($hand->isPrivatePlusDeclareComplete()
            && $mockTileList->count() <= $hand->getPrivate()->count()
        ) {
            return true;
        }

        return false;
    }

    function executeImpl() {
        $areas = $this->getContext()->getRound()->getTileAreas();
        $areas->debugMockHand($this->getActPlayer(), $this->getMockTileList());
    }
}
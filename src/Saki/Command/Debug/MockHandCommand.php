<?php
namespace Saki\Command\Debug;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class MockHandCommand extends PlayerCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $seatWind, TileList $mockTileList) {
        parent::__construct($context, [$seatWind, $mockTileList]);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    function matchPhase() {
        return $this->getPhase()->isPrivateOrPublic();
    }

    function matchActor() {
        return true;
    }

    function matchOther() {
        $mockTileList = $this->getMockTileList();

        $hand = $this->getActPlayer()->getArea()->getHand();
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
        $areas = $this->getContext()->getRound()->getAreas();
        $areas->debugMockHand($this->getActPlayer(), $this->getMockTileList());
    }
}
<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\Tile\TileList;

/**
 * @package Saki\Command\Debug
 */
class MockHandCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }
    //endregion

    /**
     * @return Area
     */
    function getActorArea() {
        $actor = $this->getParam(0);
        return $this->getRound()->getArea($actor);
    }

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        // currently Robbing phase mockHand not supported
        // since Target replace maybe complex
        $phase = $round->getPhase();
        $matchPhase = $phase->isPrivate()
            || ($phase->isPublic() && !$round->getPhaseState()->isRobbing());

        $mockTileList = $this->getMockTileList();
        $hand = $this->getActorArea()->getHand();
        $matchHand = ($mockTileList->count() <= $hand->getPublic()->count())
            || ($hand->isComplete() && $mockTileList->count() <= $hand->getPrivate()->count());

        return $matchPhase && $matchHand;
    }

    protected function executeImpl(Round $round) {
        $actorArea = $this->getActorArea();
        $actorArea->setHand($actorArea->getHand()->toMockHand($this->getMockTileList()));
    }
    //endregion
}
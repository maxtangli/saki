<?php
namespace Saki\Command\Debug;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PlayerCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\Debug
 */
class MockHandCommand extends PlayerCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        return new ArrayList();
    }

    //endregion

    /**
     * @return TileList
     */
    function getMockTileList() {
        return $this->getParam(1);
    }

    //region PlayerCommand impl
    protected static function matchPhase(Round $round, Area $actorArea) {
        // currently Robbing phase mockHand not supported
        // since Target replace maybe complex
        $phase = $round->getPhase();
        return $phase->isPrivate()
            || ($phase->isPublic() && !$round->getPhaseState()->isRobbing());
    }

    protected static function matchActor(Round $round, Area $actorArea) {
        return true;
    }

    protected function matchOther(Round $round, Area $actorArea) {
        $mockTileList = $this->getMockTileList();
        $hand = $actorArea->getHand();
        return ($mockTileList->count() <= $hand->getPublic()->count())
        || ($hand->isComplete() && $mockTileList->count() <= $hand->getPrivate()->count());
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actorArea->setHand($actorArea->getHand()->toMockHand($this->getMockTileList()));
    }
    //endregion
}
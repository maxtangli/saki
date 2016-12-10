<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Riichi;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand\PrivateCommand
 */
class RiichiCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
        $hand = $actorArea->getHand();
        $futureWaitingList = $waitingAnalyzer->analyzePrivate($hand->getPrivate(), $hand->getMelded());
        $otherParamsList = $futureWaitingList->toDiscardList();
        return $otherParamsList;
    }
    //endregion

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    /**
     * @return Riichi
     */
    protected function getRiichi() {
        return new Riichi($this->getActor(), $this->getTile());
    }

    //region PrivateCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
        return $this->getRiichi()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getRiichi()->apply($actorArea);
        $round->toNextPhase();
    }
    //endregion
}
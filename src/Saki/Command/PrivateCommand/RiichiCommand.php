<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Riichi;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand
 */
class RiichiCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        // todo optimize
        $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
        $hand = $actorArea->getHand();
        $futureWaitingList = $waitingAnalyzer->analyzePrivate($hand->getPrivate(), $hand->getMelded());
        $otherParamsList = $futureWaitingList->toDiscardList();
        return static::createMany($round, $actor, $otherParamsList, true);
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
    protected function matchOther(Round $round, Area $actorArea) {
        return $this->getRiichi()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getRiichi()->apply($actorArea);
        $round->toNextPhase();
    }
    //endregion
}
<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Open;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand\PrivateCommand
 */
class DiscardCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $uniquePrivate = $actorArea->getHand()->getPrivate()
            ->distinct()
            ->orderByTileID();
        $otherParamsList = $uniquePrivate;
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
     * @return Open
     */
    protected function getOpen() {
        return new Open($this->getActorArea(), $this->getTile(), true);
    }

    //region PrivateCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
        return $this->getOpen()->valid();
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getOpen()->apply();
        $round->toNextPhase();
    }
    //endregion
}
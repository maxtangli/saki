<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\QuadMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PrivateCommand
 */
class ConcealedKongCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $private = $actorArea->getHand()->getPrivate();
        $keySelect = function (Tile $tile) {
            return $tile->toFormatString(false);
        };
        $groupFilter = function (ArrayList $group) {
            return $group->count() == 4;
        };
        $tileGroupList = (new ArrayList())->fromGroupBy($private, $keySelect, $groupFilter);

        $toArray = function (ArrayList $list) {
            return [new TileList($list->toArray())];
        };
        $otherParamsList = $tileGroupList->select($toArray);
        return static::createMany($round, $actor, $otherParamsList);
    }
    //endregion

    /**
     * @return TileList
     */
    function getTileList() {
        return $this->getParam(1);
    }

    /**
     * @return Claim
     */
    protected function getClaim() {
        $tiles = $this->getTileList()->toArray();
        return Claim::create(
            $this->getActor(),
            $this->getRound()->getTurn(),
            $tiles,
            QuadMeldType::create(),
            true
        );
    }

    //region PrivateCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $this->getTileList()->count() == 4
        && $this->getClaim()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getClaim()->apply($actorArea);
        // stay in private phase
    }
    //endregion
}
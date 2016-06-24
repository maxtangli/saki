<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\MeldType;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand
 */
abstract class PublicClaimCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $targetTile = $hand->getTarget()->getTile();
        $public = $hand->getPublic();

        $exist = function (TileList $params) use ($public) {
            // handle red
            return $public->valueExist($params->toArray(), Tile::getEqual(true));
        };
        $otherParamsList = static::getOtherParamsListImpl($targetTile->toNotRed())
            ->where($exist);

        return static::createMany($round, $actor, $otherParamsList);
    }
    //endregion

    /**
     * @return TileList
     */
    function getTileList() {
        /** @var TileList $tileList */
        $tileList = $this->getParam(1);
        $validCount = (1 + $tileList->count()) == $this->getClaimMeldType()->getTileCount();
        if (!$validCount) {
            throw new \InvalidArgumentException(
                sprintf('$tileList[%s].', $tileList)
            );
        }
        return $tileList;
    }

    /**
     * @return Tile[]
     */
    function getClaimTiles() {
        $targetTile = $this->getActorArea()->getHand()
            ->getTarget()->getTile();
        return $this->getTileList()->getCopy()
            ->insertFirst($targetTile)
            ->toArray();
    }

    /**
     * @return Claim
     */
    function getClaim() {
        return Claim::create(
            $this->getActor(),
            $this->getRound()->getTurn(),
            $this->getClaimTiles(),
            $this->getClaimMeldType(),
            false
        );
    }

    //region PublicCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $this->getClaim()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $round->toNextPhase(
            new PrivatePhaseState($this->getActor(), false, $this->getClaim())
        );
    }
    //endregion

    //region subclass hooks
    /**
     * @return MeldType
     */
    abstract function getClaimMeldType();

    /**
     * @param Tile $notRedTargetTile
     * @return ArrayList
     */
    abstract protected static function getOtherParamsListImpl(Tile $notRedTargetTile);
    //endregion
}
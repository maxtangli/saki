<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileListParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Meld\MeldType;
use Saki\Game\Phase\PrivatePhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand\PublicCommand
 */
abstract class PublicClaimCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileListParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $targetTile = $hand->getTarget()->getTile();
        $public = $hand->getPublic();

        $exist = function (TileList $params) use ($public) {
            // handle red
            return $public->valueExist($params->toArray(), Tile::getPrioritySelector());
        };
        $otherParamsListRaw = static::getOtherParamsListImpl($targetTile->toNotRed());
        if (!$otherParamsListRaw instanceof ArrayList) {
            throw new \InvalidArgumentException(
                sprintf('Invalid getExecutableListImpl() return type in class[%s]', static::getName())
            );
        }
        $otherParamsList = $otherParamsListRaw->where($exist);
        return $otherParamsList;
    }
    //endregion

    /**
     * @return TileList
     */
    function getTileList() {
        return $this->getParam(1);
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
        return Claim::createPublic(
            $this->getActorArea(),
            $this->getRound()->getTurn(),
            $this->getClaimTiles(),
            $this->getClaimMeldType(),
            $this->getActorArea()->getHand()->getTarget()
        );
    }

    //region PublicCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
        $validNextSeatWind = !$this->requirePublicNextActor() || $actorArea->isPublicNextActor();
        $validCount = (1 + $this->getTileList()->count())
            == $this->getClaimMeldType()->getTileCount();
        return $validNextSeatWind && $validCount && $this->getClaim()->valid();
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $round->toNextPhase(
            new PrivatePhaseState($round, $this->getActor(), false, $this->getClaim())
        );
    }
    //endregion

    //region subclass hooks
    function requirePublicNextActor() {
        return false;
    }

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
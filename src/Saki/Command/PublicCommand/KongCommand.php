<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\QuadMeldType;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\PublicCommand
 */
class KongCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, TileParamDeclaration::class, TileParamDeclaration::class];
    }
    //endregion

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param Tile $tile1
     * @param Tile $tile2
     * @param Tile $tile3
     */
    function __construct(Round $round,
                         SeatWind $actor, Tile $tile1, Tile $tile2, Tile $tile3) {
        parent::__construct($round, [$actor, $tile1, $tile2, $tile3]);
    }

    /**
     * @return Tile
     */
    function getTile1() {
        return $this->getParam(1);
    }

    /**
     * @return Tile
     */
    function getTile2() {
        return $this->getParam(2);
    }

    /**
     * @return Tile
     */
    function getTile3() {
        return $this->getParam(3);
    }

    /**
     * @return Claim
     */
    protected function getClaim() {
        $targetTile = $this->getActorArea()->getHand()->getTarget()->getTile();
        $tiles = [$targetTile, $this->getTile1(), $this->getTile2(), $this->getTile3()];
        return Claim::create(
            $this->getActor(),
            $this->getRound()->getTurn(),
            $tiles,
            QuadMeldType::create(),
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
}
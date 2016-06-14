<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Round;
use Saki\Meld\QuadMeldType;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand
 */
class ConcealedKongCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class,
            TileParamDeclaration::class, TileParamDeclaration::class,
            TileParamDeclaration::class, TileParamDeclaration::class];
    }
    //endregion

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
     * @return Tile
     */
    function getTile4() {
        return $this->getParam(4);
    }

    /**
     * @return Claim
     */
    protected function getClaim() {
        $tiles = [$this->getTile1(), $this->getTile2(), $this->getTile3(), $this->getTile4()];
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
        return $this->getClaim()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getClaim()->apply($actorArea);
        // stay in private phase
    }
    //endregion
}
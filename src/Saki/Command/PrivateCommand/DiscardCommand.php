<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Open;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand
 */
class DiscardCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    static function getExecutables(Round $round, SeatWind $actor) {
        $private = $round->getArea($actor)
            ->getHand()->getPrivate();
        $uniquePrivate = $private->getCopy()->distinct(Tile::getEqual(true));
        $toCommand = function (Tile $tile) use ($round, $actor) {
            return new self($round, $actor, $tile);
        };
        $executable = function (Command $command) {
            return $command->executable();
        };
        return [];
        return $uniquePrivate->toArrayList($toCommand)
            ->where($executable)
            ->toArray();
    }

    //endregion

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param Tile $tile
     */
    function __construct(Round $round, SeatWind $actor, Tile $tile) {
        parent::__construct($round, [$actor, $tile]);
    }

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
        return new Open($this->getActor(), $this->getTile(), true);
    }

    //region PrivateCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $this->getOpen()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $this->getOpen()->apply($actorArea);
        $round->toNextPhase();
    }
    //endregion
}
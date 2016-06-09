<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Open;
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

    static function getExecutables(CommandContext $context, SeatWind $actor) {
        $private = $context->getAreas()->getArea($actor)
            ->getHand()->getPrivate();
        $uniquePrivate = $private->getCopy()->distinct(Tile::getEqual(true));
        $toCommand = function (Tile $tile) use ($context, $actor) {
            return new self($context, $actor, $tile);
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
     * @param CommandContext $context
     * @param SeatWind $actor
     * @param Tile $tile
     */
    function __construct(CommandContext $context, SeatWind $actor, Tile $tile) {
        parent::__construct($context, [$actor, $tile]);
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
    protected function matchOther(CommandContext $context, Area $actorArea) {
        return $this->getOpen()->valid($actorArea);
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $this->getOpen()->apply($actorArea);
        $context->getRound()->toNextPhase();
    }
    //endregion
}
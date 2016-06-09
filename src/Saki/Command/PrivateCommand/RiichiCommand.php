<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Riichi;
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
     * @return Riichi
     */
    protected function getRiichi() {
        return new Riichi($this->getActor(), $this->getTile());
    }

    //region PrivateCommand impl
    protected function matchOther(CommandContext $context, Area $actorArea) {
        return $this->getRiichi()->valid($actorArea);
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $this->getRiichi()->apply($actorArea);
        $context->getRound()->toNextPhase();
    }
    //endregion
}
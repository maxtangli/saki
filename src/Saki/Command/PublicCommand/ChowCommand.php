<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;

class ChowCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind, Tile $tile1, Tile $tile2) {
        parent::__construct($context, [$playerSeatWind, $tile1, $tile2]);
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

    function matchOther() {
        return true; // todo
    }

    function executeImpl() {
        $round = $this->getContext()->getRound();

        $round->getAreas()->chow(
            $this->getActPlayer(), $this->getTile1(), $this->getTile2(), $this->getCurrentPlayer()
        );
        $round->toNextPhase(
            new PrivatePhaseState($this->getActPlayer(), false)
        );
    }
}
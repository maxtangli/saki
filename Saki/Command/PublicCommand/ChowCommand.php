<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\Tile\Tile;

class ChowCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile1, Tile $tile2) {
        parent::__construct($context, [$playerSelfWind, $tile1, $tile2]);
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

    function matchOtherConditions() {
        $tileAreas = $this->getContext()->getRoundData()->getTileAreas();
        return $this->getActPlayer()->getTileArea()->canChowByOther(
            $tileAreas->getTargetTile()->getTile(), $this->getTile1(), $this->getTile2()
        );
    }

    function executeImpl() {
        $round = $this->getContext()->getRoundData();

        $round->getTileAreas()->chow(
            $this->getActPlayer(), $this->getTile1(), $this->getTile2(), $this->getCurrentPlayer()
        );
        $round->toNextPhase(
            new PrivatePhaseState($this->getActPlayer(), false)
        );
    }
}
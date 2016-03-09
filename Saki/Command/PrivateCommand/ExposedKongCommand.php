<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Tile\Tile;

class ExposedKongCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile) {
        parent::__construct($context, [$playerSelfWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    function matchOtherConditions() {
        $validTile = $this->getActPlayer()->getTileArea()->canConcealedKong($this->getTile());
        return $validTile;
    }

    function executeImpl() {
        $this->getContext()->getRoundData()->getTileAreas()->kongBySelf($this->getActPlayer(), $this->getTile());
        // todo handle RobAQuadPhase
        // stay in private phase
    }
}
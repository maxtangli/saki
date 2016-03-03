<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Tile\Tile;

class DiscardCommand extends PrivateCommand {
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
        $validTile = $this->getActPlayer()->getTileArea()->canDiscard($this->getTile());
        return $validTile;
    }

    function executeImpl() {
        $this->getContext()->getRoundData()->getTileAreas()->discard($this->getActPlayer(), $this->getTile());
//        $this->getContext()->getRoundData()->toPublicPhase();
        $this->getContext()->getRoundData()->toNextPhase();
    }
}
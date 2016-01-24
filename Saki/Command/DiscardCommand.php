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
        $validTile = $this->getContext()->getRound()->getCurrentPlayer()->getTileArea()->canDiscard($this->getTile());
        return $validTile;
    }

    function execute() {
        $player = $this->getContext()->getRound()->getPlayerList()->getSelfWindPlayer($this->getPlayerSelfWind());
        $this->getContext()->getRound()->discard($player, $this->getTile());
    }
}
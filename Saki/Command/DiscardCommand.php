<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Tile\Tile;

class DiscardCommand extends Command {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile) {
        parent::__construct($context, [$playerSelfWind, $tile]);
        // todo validate
    }

    /**
     * @return Tile
     */
    function getPlayerSelfWind() {
        return $this->playerSelfWind;
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->tile;
    }

    function executable() {
        // todo
    }

    function execute() {

        $this->getContext()->getRound()->discard($this->getPlayer(), $this->getTile());

        $r = $this->getContext()->getRound();

        $this->assertPrivatePhase($player);
        $r->getRoundData()->getTileAreas()->discard($this->getPlayer(), $this->getTile());

        // switch phase
        $r->toPublicPhase();
    }
}
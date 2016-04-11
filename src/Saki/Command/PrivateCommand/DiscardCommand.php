<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Tile\Tile;

class DiscardCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    function matchOther() {
        $hand = $this->getContext()->getActorHand();
        $validTile = $hand->getPrivate()->valueExist($this->getTile());
        return $validTile;
    }

    function executeImpl() {
        $context = $this->getContext();
        $context->getAreas()->discard($this->getActPlayer(), $this->getTile());
        $context->getRound()->toNextPhase();
    }
}
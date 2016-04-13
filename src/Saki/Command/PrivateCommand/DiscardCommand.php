<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class DiscardCommand extends PrivateCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    protected function matchOther(CommandContext $context) {
        $hand = $this->getContext()->getActorHand();
        $validTile = $hand->getPrivate()->valueExist($this->getTile());
        return $validTile;
    }

    protected function executeImpl(CommandContext $context) {
        $context->getAreas()->discard($this->getActor(), $this->getTile());
        $context->getRound()->toNextPhase();
    }
}
<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Claim;
use Saki\Game\Open;
use Saki\Game\SeatWind;
use Saki\Meld\Meld;
use Saki\Meld\QuadMeldType;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;

class ExtendKongCommand extends PrivateCommand {
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
        return true; // todo
    }

    protected function executeImpl(CommandContext $context) {
        $area = $context->getActorArea();
        $actor = $this->getActor();
        $hand = $context->getActorHand();
        $tile = $this->getTile();
        $turn = $context->getTurn();

        // set target tile
        $open = new Open($actor, $tile, false);
        $open->apply($area);

        // to RobbingPublicPhase
        $postEnter = function () use ($area) {
            $area->extendKongAfter();
        };
        $robbingPublicPhase = PublicPhaseState::createRobbing($this->getActor(), $postEnter);
        $context->getRound()->toNextPhase($robbingPublicPhase);
    }
}
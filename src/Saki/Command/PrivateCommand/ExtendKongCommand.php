<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Phase\RobbingPubicPhaseState;
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
        $r = $context->getRound();
        $actor = $this->getActor();
        $tile = $this->getTile();

        // set target tile
        $context->getAreas()->extendKongBefore($actor, $tile);

        // to RobbingPublicPhase
        $postLeave = function () use ($r) {
            $r->getAreas()->extendKongAfter($this->getActor(), $this->getTile());
        };
        $robbingPublicPhase = new RobbingPubicPhaseState($postLeave);
        $r->toNextPhase($robbingPublicPhase);
    }
}
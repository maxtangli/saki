<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\SeatWind;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;

class PlusKongCommand extends PrivateCommand {
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
        $context->getAreas()->plusKongBefore($actor, $tile);

        // to RobQuadPhase
        $robQuadPhase = new PublicPhaseState();

        $robQuadPhase->setRobQuad(true);

        $postLeave = function () use ($r) {
            $r->getAreas()->plusKongAfter($this->getActor(), $this->getTile());
        };
        $robQuadPhase->setPostLeave($postLeave);

        $retPrivatePhase = new PrivatePhaseState($actor, false, true);
        $robQuadPhase->setCustomNextState($retPrivatePhase);

        $r->toNextPhase($robQuadPhase);
    }
}
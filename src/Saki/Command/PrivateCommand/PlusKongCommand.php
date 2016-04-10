<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;

class PlusKongCommand extends PrivateCommand {
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
        return true; // todo
    }

    function executeImpl() {
        $r = $this->getContext()->getRound();

        // set target tile
        $r->getAreas()->plusKongBefore($this->getActPlayer(), $this->getTile());

        // to RobAQuadPhase
        $robQuadPhase = new PublicPhaseState();

        $robQuadPhase->setRobQuad(true);

        $postLeave = function () use ($r) {
            $r->getAreas()->plusKongAfter($this->getActPlayer(), $this->getTile());
        };
        $robQuadPhase->setPostLeave($postLeave);

        $retPrivatePhase = new PrivatePhaseState($this->getActPlayer(), false, true);
        $robQuadPhase->setCustomNextState($retPrivatePhase);

        $r->toNextPhase($robQuadPhase);
    }
}
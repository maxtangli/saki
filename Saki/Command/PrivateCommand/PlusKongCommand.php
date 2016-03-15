<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\RoundPhase\PublicPhaseState;
use Saki\Tile\Tile;

class PlusKongCommand extends PrivateCommand {
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
        $validTile = $this->getActPlayer()->getTileArea()->canPlusKong($this->getTile());
        return $validTile;
    }

    function executeImpl() {
        $r = $this->getContext()->getRoundData();

        // set target tile
        $r->getTileAreas()->plusKongBefore($this->getActPlayer(), $this->getTile());

        // to RobAQuadPhase
        $robQuadPhase = new PublicPhaseState();

        $robQuadPhase->setRobQuad(true);

        $postLeave = function () use ($r) {
            $r->getTileAreas()->plusKong($this->getActPlayer(), $this->getTile());
        };
        $robQuadPhase->setPostLeave($postLeave);

        $retPrivatePhase = new PrivatePhaseState($this->getActPlayer(), false, true);
        $robQuadPhase->setCustomNextState($retPrivatePhase);

        $r->toNextPhase($robQuadPhase);
    }
}
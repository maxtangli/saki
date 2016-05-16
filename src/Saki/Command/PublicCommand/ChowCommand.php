<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Claim;
use Saki\Game\SeatWind;
use Saki\Meld\Meld;
use Saki\Meld\RunMeldType;
use Saki\Phase\PrivatePhaseState;
use Saki\Tile\Tile;

class ChowCommand extends PublicCommand {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind, Tile $tile1, Tile $tile2) {
        parent::__construct($context, [$playerSeatWind, $tile1, $tile2]);
    }

    /**
     * @return Tile
     */
    function getTile1() {
        return $this->getParam(1);
    }

    /**
     * @return Tile
     */
    function getTile2() {
        return $this->getParam(2);
    }

    protected function matchOther(CommandContext $context) {
        return true; // todo
    }

    protected function executeImpl(CommandContext $context) {
        $area = $context->getActorArea();
        $actor = $this->getActor();
        $turn = $context->getTurn();

        $targetTile = $area->getHand()->getTarget()->getTile();
        $tiles = [$targetTile, $this->getTile1(), $this->getTile2()];
        $claim = Claim::create($actor, $turn,
            $tiles, RunMeldType::create(), false
        );

        $context->getRound()->toNextPhase(
            new PrivatePhaseState($actor, false, $claim)
        );
    }
}
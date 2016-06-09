<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\MeldParamDeclaration;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Open;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;
use Saki\Meld\Meld;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;

/**
 * @package Saki\Command\PrivateCommand
 */
class ExtendKongCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, MeldParamDeclaration::class];
    }
    //endregion

    /**
     * @param CommandContext $context
     * @param SeatWind $actor
     * @param Tile $tile
     * @param Meld $meld
     */
    function __construct(CommandContext $context, SeatWind $actor, Tile $tile, Meld $meld) {
        parent::__construct($context, [$actor, $tile, $meld]);
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->getParam(1);
    }

    /**
     * @return Meld
     */
    function getMeld() {
        return $this->getParam(2);
    }

    /**
     * @return Claim
     */
    protected function getClaim() {
        return Claim::createFromMelded(
            $this->getActor(),
            $this->getContext()->getAreas()->getTurn(),
            $this->getTile(),
            $this->getMeld()
        );
    }

    //region PrivateCommand impl
    protected function matchOther(CommandContext $context, Area $actorArea) {
        return $this->getClaim()->valid($actorArea);
    }

    protected function executePlayerImpl(CommandContext $context, Area $actorArea) {
        $actor = $this->getActor();
        $tile = $this->getTile();

        // set target tile
        $open = new Open($actor, $tile, false);
        $open->apply($actorArea);

        // to RobbingPublicPhase
        $claim = $this->getClaim();
        $target = new Target($tile, TargetType::create(TargetType::KEEP), $actorArea->getSeatWind());
        $context->getRound()->toNextPhase(
            PublicPhaseState::createRobbing($actor, $claim, $target)
        );
    }
    //endregion
}
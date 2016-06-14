<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\MeldParamDeclaration;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Open;
use Saki\Game\Round;
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
            $this->getRound()->getTurn(),
            $this->getTile(),
            $this->getMeld()
        );
    }

    //region PrivateCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $this->getClaim()->valid($actorArea);
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actor = $this->getActor();
        $tile = $this->getTile();

        // set target tile
        $open = new Open($actor, $tile, false);
        $open->apply($actorArea);

        // to RobbingPublicPhase
        $claim = $this->getClaim();
        $target = new Target($tile, TargetType::create(TargetType::KEEP), $actorArea->getSeatWind());
        $round->toNextPhase(
            PublicPhaseState::createRobbing($actor, $claim, $target)
        );
    }
    //endregion
}
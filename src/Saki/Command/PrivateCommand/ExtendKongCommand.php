<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\MeldParamDeclaration;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Claim;
use Saki\Game\Meld\Meld;
use Saki\Game\Meld\PungMeldType;
use Saki\Game\Open;
use Saki\Game\Phase\PublicPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;
use Saki\Game\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command\PrivateCommand\PrivateCommand
 */
class ExtendKongCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, MeldParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $private = $hand->getPrivate();
        $pungs = $hand->getMelded()->toFiltered([PungMeldType::create()]);

        $toOtherParamsList = function (Meld $pung) use ($private) {
            /** @var Tile $tile */
            $tile = $pung[0];
            $toParams = function (Tile $tile) use ($pung) {
                return [$tile, $pung];
            };
            return $private->toArrayList()
                ->where(Utils::toPredicate($tile))
                ->distinct()// handle red
                ->select($toParams);
        };
        $otherParamsList = (new ArrayList())
            ->fromSelectMany($pungs, $toOtherParamsList);
        return $otherParamsList;
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
        return Claim::createExtendKong(
            $this->getActorArea(),
            $this->getRound()->getTurnHolder()->getTurn(),
            $this->getTile(),
            $this->getMeld()
        );
    }

    //region PrivateCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
        return $this->getClaim()->valid();
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actor = $this->getActor();
        $tile = $this->getTile();

        // set target tile
        $open = new Open($actorArea, $tile, false);
        $open->apply();

        // to RobbingPublicPhase
        $claim = $this->getClaim();
        $target = new Target($tile, TargetType::create(TargetType::KEEP), $actorArea->getSeatWind());
        $round->toNextPhase(
            PublicPhaseState::createRobbing($round, $actor, $claim, $target)
        );
    }
    //endregion
}
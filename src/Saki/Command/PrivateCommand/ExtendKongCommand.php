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
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;
use Saki\Meld\Meld;
use Saki\Meld\TripleMeldType;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command\PrivateCommand
 */
class ExtendKongCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class, MeldParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $private = $hand->getPrivate();
        $triples = $hand->getMelded()->toFiltered([TripleMeldType::create()]);

        $toOtherParamsList = function (Meld $triple) use ($private) {
            /** @var Tile $tile */
            $tile = $triple[0];
            $toParams = function (Tile $tile) use ($triple) {
                return [$tile, $triple];
            };
            return $private->toArrayList()
                ->where(Utils::toPredicate($tile))
                ->distinct()// handle red
                ->select($toParams);
        };
        $otherParamsList = (new ArrayList())
            ->fromSelectMany($triples, $toOtherParamsList);

        return static::createMany($round, $actor, $otherParamsList, true); // validate drawReplacementAble
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
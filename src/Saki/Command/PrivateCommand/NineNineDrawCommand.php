<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Util\ArrayList;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Command\PrivateCommand
 */
class NineNineDrawCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        return static::createMany($round, $actor, new ArrayList([[]]), true);
    }
    //endregion

    //region PrivateCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $round->getTurn()->isFirstCircle()
        && !$round->getClaimHistory()->hasClaim()
        && $actorArea->getHand()->getPrivate()->isNineKindsOfTermOrHonour();
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $result = new AbortiveDrawResult(
            $round->getRule()->getPlayerType(),
            ResultType::create(ResultType::NINE_NINE_DRAW)
        );
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
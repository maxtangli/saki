<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Command\PrivateCommand\PrivateCommand
 */
class NineNineDrawCommand extends PrivateCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $otherParamsList = new ArrayList([[]]);
        return $otherParamsList;
    }
    //endregion

    //region PrivateCommand impl
    protected function executablePlayerImpl(Round $round, Area $actorArea) {
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
            new OverPhaseState($round, $result)
        );
    }
    //endregion
}
<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Phase\OverPhaseState;
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
    //endregion

    //region PrivateCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        return $round->getTurn()->isFirstCircle()
        && !$round->getClaimHistory()->hasClaim()
        && $actorArea->getHand()->getPrivate()->isNineKindsOfTermOrHonour();
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $result = new AbortiveDrawResult(
            $round->getGameData()->getPlayerType(),
            ResultType::create(ResultType::NINE_NINE_DRAW)
        );
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
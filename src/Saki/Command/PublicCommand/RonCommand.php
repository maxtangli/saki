<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinState;

/**
 * @package Saki\Command\PublicCommand
 */
class RonCommand extends PublicCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class];
    }

    static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea) {
        $otherParamsList = new ArrayList([[]]);
        return $otherParamsList;
    }

    //endregion

    //region PublicCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        $winReport = $round->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_OTHER;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actor = $this->getActor();

        $winReport = $round->getWinReport($actor);
        $result = new WinResult(WinResultInput::createRon(
            [[$actor, $winReport->getFanAndFu()]],
            $round->getCurrentSeatWind(),
            $round->getOtherSeatWinds([$actor, $round->getCurrentSeatWind()]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailing()->getSeatWindTurn(),
            [$winReport]
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
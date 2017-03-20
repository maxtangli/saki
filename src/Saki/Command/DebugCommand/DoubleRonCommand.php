<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinReport;

/**
 * @package Saki\Command\Debug
 */
class DoubleRonCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, SeatWindParamDeclaration::class];
    }
    //endregion

    /**
     * @return ArrayList
     */
    function getActorList() {
        return new ArrayList([$this->getParam(0), $this->getParam(1)]);
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        // suppose already validated in RonCommand
        return $round->getPhase()->isPublic();
    }

    protected function executeImpl(Round $round) {
        $actorList = $this->getActorList();

        $getWinReport = function (SeatWind $actor) use ($round) {
            return $round->getWinReport($actor);
        };
        $winReportList = $actorList->toArrayList($getWinReport);

        $toWinPair = function (WinReport $winReport) {
            return [$winReport->getActor(), $winReport->getFanAndFu()];
        };
        $winnerPairs = $winReportList->toArray($toWinPair);

        $result = new WinResult(WinResultInput::createRon(
            $winnerPairs,
            $round->getCurrentSeatWind(),
            $round->getAreaList()->getOtherSeatWinds([$actorList[0], $actorList[1], $round->getCurrentSeatWind()]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailing()->getSeatWindTurn(),
            $winReportList->toArray()
        ));
        $round->toNextPhase(
            new OverPhaseState($round, $result)
        );
    }
    //endregion
}
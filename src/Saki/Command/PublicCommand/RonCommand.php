<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Phase\OverPhaseState;
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
    //endregion

    //region PublicCommand impl
    protected function matchOther(Round $round, Area $actorArea) {
        $winReport = $round->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_OTHER;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actor = $this->getActor();

        $result = new WinResult(WinResultInput::createRon(
            [[$actor, $round->getWinReport($actor)->getFanAndFu()]],
            $round->getCurrentSeatWind(),
            $round->getOtherSeatWinds([$actor, $round->getCurrentSeatWind()]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailingCurrent()->getSeatWindTurn()
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
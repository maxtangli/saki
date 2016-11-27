<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinState;

/**
 * @package Saki\Command\PrivateCommand\PrivateCommand
 */
class TsumoCommand extends PrivateCommand {
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
        $winReport = $round->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_SELF;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {

        $actor = $this->getActor();

        $round->getWall()->getIndicatorWall()->openUraIndicators();

        $winReport = $round->getWinReport($actor);
        $result = new WinResult(WinResultInput::createTsumo(
            [$actor, $winReport->getFanAndFu()],
            $round->getOtherSeatWinds([$actor]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailing()->getSeatWindTurn(),
            $winReport
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
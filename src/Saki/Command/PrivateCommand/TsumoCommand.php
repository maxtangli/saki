<?php

namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Area;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Pao\Pao;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinState;
use Saki\Win\Yaku\Fan1\AfterAKongWinYaku;

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
        $phaseState = $round->getPhaseState();
        if ($phaseState->hasClaim() && $phaseState->getClaim()->isChowOrPung()) {
            return false;
        }

        $winReport = $round->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_SELF;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {
        $actor = $this->getActor();
        $winReport = $round->getWinReport($actor);

        // open ura indicators
        $round->getWall()->getIndicatorWall()->openUraIndicators();

        // to over phase
        $result = new WinResult(WinResultInput::createTsumo(
            [$actor, $winReport->getFanAndFu()],
            $round->getAreaList()->getOtherSeatWinds([$actor]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailing()->getSeatWindTurn(),
            $winReport,
            $round->getAreaList()->generatePaoList([$actor])
        ));
        $round->toNextPhase(
            new OverPhaseState($round, $result)
        );
    }
    //endregion
}
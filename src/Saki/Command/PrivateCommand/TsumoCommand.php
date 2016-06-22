<?php
namespace Saki\Command\PrivateCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\PrivateCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Phase\OverPhaseState;
use Saki\Util\ArrayList;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinState;

/**
 * @package Saki\Command\PrivateCommand
 */
class TsumoCommand extends PrivateCommand {
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
        $winReport = $round->getWinReport($this->getActor());
        return $winReport->getWinState()->getValue() == WinState::WIN_BY_SELF;
    }

    protected function executePlayerImpl(Round $round, Area $actorArea) {

        $actor = $this->getActor();

        $round->getWall()->getDeadWall()->openUraDoraIndicator();

        $result = new WinResult(WinResultInput::createTsumo(
            [$actor, $round->getWinReport($actor)->getFanAndFu()],
            $round->getOtherSeatWinds([$actor]),
            $round->getRiichiHolder()->getRiichiPoints(),
            $round->getPrevailingCurrent()->getSeatWindTurn()
        ));
        $round->toNextPhase(
            new OverPhaseState($result)
        );
    }
    //endregion
}
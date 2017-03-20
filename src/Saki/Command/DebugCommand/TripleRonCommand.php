<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Result\TripleRonDrawResult;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;
use Saki\Win\WinReport;

/**
 * @package Saki\Command\Debug
 */
class TripleRonCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, SeatWindParamDeclaration::class, SeatWindParamDeclaration::class];
    }
    //endregion

    /**
     * @return ArrayList
     */
    function getActorList() {
        return new ArrayList([$this->getParam(0), $this->getParam(1), $this->getParam(2)]);
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        // suppose already validated in RonCommand
        return $round->getPhase()->isPublic();
    }

    protected function executeImpl(Round $round) {
        $result = new TripleRonDrawResult(
            $round->getRule()->getPlayerType(),
            $this->getActorList()->toArray()
        );
        $round->toNextPhase(
            new OverPhaseState($round, $result)
        );
    }
    //endregion
}
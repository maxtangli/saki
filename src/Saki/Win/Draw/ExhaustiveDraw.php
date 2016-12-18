<?php
namespace Saki\Win\Draw;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Win\Result\ExhaustiveDrawResult;

/**
 * @package Saki\Win\Draw
 */
class ExhaustiveDraw extends Draw {
    //region Draw impl
    protected function isDrawImpl(Round $round) {
        $nextState = $round->getPhaseState()->getNextState();
        $isExhaustiveDraw = $nextState->getPhase()->isPrivate()
            && $nextState->shouldDraw()
            && $round->getWall()->getDrawWall()->isEmpty();
        return $isExhaustiveDraw;
    }

    protected function getResultImpl(Round $round) {
        $waitingAnalyzer = $round->getRule()->getWinAnalyzer()->getWaitingAnalyzer();
        $isWaiting = function (Area $area) use ($waitingAnalyzer) {
            $public = $area->getHand()->getPublic();
            $melded = $area->getHand()->getMelded();
            $waitingTileList = $waitingAnalyzer->analyzePublic($public, $melded);
            $isWaiting = $waitingTileList->isNotEmpty();
            return $isWaiting;
        };
        $waitingArray = $round->getAreaList()->toArray($isWaiting);
        $result = ExhaustiveDrawResult::fromWaitingArray($waitingArray);
        return $result;
    }
    //endregion
}
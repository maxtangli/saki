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
        $nextState = $round->getAreas()->getPhaseState()->getNextState($round);
        $isExhaustiveDraw = $nextState->getPhase()->isPrivate()
            && $nextState->shouldDraw()
            && $round->getAreas()->getWall()->getRemainTileCount() == 0;
        return $isExhaustiveDraw;
    }

    protected function getResultImpl(Round $round) {
        $waitingAnalyzer = $round->getAreas()->getGameData()->getWinAnalyzer()->getWaitingAnalyzer();
        $areaList = $round->getAreas()->getAreaList();
        $isWaiting = function (Area $area) use ($waitingAnalyzer) {
            $public = $area->getHand()->getPublic();
            $declare = $area->getHand()->getMelded();
            $waitingTileList = $waitingAnalyzer->analyzePublic($public, $declare);
            $isWaiting = $waitingTileList->count() > 0;
            return $isWaiting;
        };
        $waitingArray = $areaList->toArrayList($isWaiting)->toArray();
        $result = ExhaustiveDrawResult::fromWaitingArray($waitingArray);
        return $result;
    }
    //endregion
}
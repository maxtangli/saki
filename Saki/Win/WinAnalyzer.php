<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;
use Saki\Win\Fu\FuCountAnalyzer;
use Saki\Win\Fu\FuCountTarget;
use Saki\Win\Yaku\YakuAnalyzer;
use Saki\Win\Yaku\YakuItemList;
use Saki\Win\Yaku\YakuSet;

class WinAnalyzer {
    private $yakuAnalyzer;
    private $tileSeriesAnalyzer;
    private $waitingAnalyzer;

    function __construct(YakuSet $yakuSet) {
        $this->yakuAnalyzer = new YakuAnalyzer($yakuSet);
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer();
        $this->waitingAnalyzer = new WaitingAnalyzer();
    }

    function getYakuAnalyzer() {
        return $this->yakuAnalyzer;
    }

    function getTileSeriesAnalyzer() {
        return $this->tileSeriesAnalyzer;
    }

    function getWaitingAnalyzer() {
        return $this->waitingAnalyzer;
    }

    /**
     * find all possible result and return the highest yaku-count ones.
     * @param WinTarget $target
     * @return WinResult
     */
    function analyzeTarget(WinTarget $target) {
        // handTiles target -> handMelds[] subTarget
        $analyzer = new MeldCompositionsAnalyzer();
        $handTileList = $target->getPrivateHand();
        $handMeldTypes = [
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
            PairMeldType::getInstance(),
        ];
        $handMeldCompositions = $analyzer->analyzeMeldCompositions($handTileList, $handMeldTypes);
        if (empty($handMeldCompositions)) {
            return new WinResult(WinState::getInstance(WinState::NOT_WIN), new YakuItemList([]), 0, new TileSortedList([]));
        }

        // get winResult[] of each subTarget
        $subTargets = array_map(function (MeldList $handMeldList) use ($target) {
            return $target->toSubTarget($handMeldList);
        }, $handMeldCompositions);
        $subResults = array_map(function (WinSubTarget $subTarget) {
            return $this->analyzeSubTarget($subTarget);
        }, $subTargets);

        /*
         * merge subResults into final result
         *
         * final yakuList/fuCount: max subResult.xx
         * final winState: handle furiten and waiting
         * final waitingTiles: waitingAnalyzer->analyzePublic
         */

        // get best winSubResult as final result
        /** @var WinSubResult $targetSubResult */
        $targetSubResult = (new ArrayLikeObject($subResults))->getMax(WinSubResult::getComparator());
        $finalWinState = $targetSubResult->getWinState();

        // handle furiten
        if ($targetSubResult->getWinState()->isTrueWin()) {
            $waitingTileList = $this->getWaitingAnalyzer()->analyzePublic(
                $target->getPublicHand(), $target->getDeclaredMeldList()
            );

            $isFuritenFalseWin = $this->isFuritenFalseWin($target, $waitingTileList);
            if ($isFuritenFalseWin) {
                $finalWinState = WinState::getInstance(WinState::FURITEN_FALSE_WIN);
            }
        }

        // final winResult
        $result = new WinResult($finalWinState, $targetSubResult->getYakuList(), $targetSubResult->getFuCount());
        return $result;
    }

    /**
     * note that waiting/furiten winState is not considered in this phase.
     * @param WinSubTarget $subTarget
     * @return WinSubResult
     */
    function analyzeSubTarget(WinSubTarget $subTarget) {
        $tileSeries = $this->getTileSeriesAnalyzer()->analyzeTileSeries($subTarget->getAllMeldList());
        if (!$tileSeries->exist()) {
            return new WinSubResult(WinState::getInstance(WinState::NOT_WIN), new YakuItemList([]), 0);
        }

        $yakuList = $this->getYakuAnalyzer()->analyzeYakuList($subTarget);
        if ($yakuList->count() == 0) {
            return new WinSubResult(WinState::getInstance(WinState::NO_YAKU_FALSE_WIN), new YakuItemList([]), 0);
        }

        $winStateValue = $subTarget->isPrivatePhase() ? WinState::WIN_BY_SELF : WinState::WIN_BY_OTHER;
        $winState = WinState::getInstance($winStateValue);

        $waitingType = $tileSeries->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile(), $subTarget->getDeclaredMeldList());
        $fuCountTarget = new FuCountTarget($subTarget, $yakuList, $waitingType);
        $fuCountResult = FuCountAnalyzer::getInstance()->getResult($fuCountTarget);
        $fuCount = $fuCountResult->getTotalFuCount();

        return new WinSubResult($winState, $yakuList, $fuCount);
    }

    /**
     * precondition: match other win conditions. Otherwise, return value may be wrong.
     * @param WinTarget $target
     * @param TileList $waitingTileList
     * @return bool
     */
    function isFuritenFalseWin(WinTarget $target, TileList $waitingTileList) {
        if ($target->isPrivatePhase()) {
            return false;
        }

        $finalNgList = new TileList([]);
        $openHistory = $target->getOpenHistory();

        // ng case 1: self discarded tiles
        $selfNgList = $openHistory->getSelf($target->getSelfWind());
        $finalNgList->merge($selfNgList);

        // ng case 2: all other player's opened tiles since
        if ($target->isReach()) { // ng case 2: since self reach
            $fromTurn = $target->getReachTurn();
        } else { // ng case 3: since last turn where self discarded
            $globalTurn = $target->getGlobalTurn();
            if ($globalTurn == 1) {
                $fromTurn = 1;
            } else {
                $targetSelfWind = $target->getSelfWind();
                $currentSelfWind = $target->getCurrentPlayer()->getSelfWind();
                $selfTurnPassed = $targetSelfWind->getWindOffset($currentSelfWind) <= 0;
                $fromTurn = $selfTurnPassed ? $globalTurn : $globalTurn - 1;
            }// todo more simpler logic possible?
        }
        $excludedLastTile = true; // remember to exclude current target tile
        $otherNgList = $openHistory->getOther($target->getSelfWind(), $fromTurn, $target->getSelfWind(), $excludedLastTile);
        $finalNgList->merge($otherNgList);

        $isFuriten = $finalNgList->any(function (Tile $ngTile) use ($waitingTileList) {
            return $waitingTileList->valueExist($ngTile);
        });
        return $isFuriten;
    }
}


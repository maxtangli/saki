<?php
namespace Saki\Win;

use Saki\Meld\MeldCombinationAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Fu\FuCountAnalyzer;
use Saki\Win\Fu\FuCountTarget;
use Saki\Win\Yaku\YakuAnalyzer;
use Saki\Win\Yaku\YakuItemList;
use Saki\Win\Yaku\YakuSet;

/**
 * Analyze WinResult for a given player.
 * @package Saki\Win
 */
class WinAnalyzer {
    private $yakuAnalyzer;
    private $tileSeriesAnalyzer;
    private $waitingAnalyzer;
    private $handMeldCombinationAnalyzer;

    /**
     * @param YakuSet $yakuSet
     */
    function __construct(YakuSet $yakuSet) {
        $this->yakuAnalyzer = new YakuAnalyzer($yakuSet);
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer();
        $this->waitingAnalyzer = new WaitingAnalyzer(
            $this->tileSeriesAnalyzer
        );

        $handMeldTypes = [
            RunMeldType::create(),
            TripleMeldType::create(),
            PairMeldType::create(),
        ];
        $this->handMeldCombinationAnalyzer = new MeldCombinationAnalyzer($handMeldTypes);
    }

    /**
     * @return YakuAnalyzer
     */
    function getYakuAnalyzer() {
        return $this->yakuAnalyzer;
    }

    /**
     * @return TileSeriesAnalyzer
     */
    function getTileSeriesAnalyzer() {
        return $this->tileSeriesAnalyzer;
    }

    /**
     * @return WaitingAnalyzer
     */
    function getWaitingAnalyzer() {
        return $this->waitingAnalyzer;
    }

    /**
     * @return MeldCombinationAnalyzer
     */
    function getHandMeldCombinationAnalyzer() {
        return $this->handMeldCombinationAnalyzer;
    }

    /**
     * Find all possible WinSubResults and merge them into a final WinResult.
     * @param WinTarget $target
     * @return WinResult
     */
    function analyzeTarget(WinTarget $target) {
        // 1. handTileList target -> handMeldList's List
        $handTileList = $target->getPrivateHand();
        $handMeldCombinationList = $this->getHandMeldCombinationAnalyzer()
            ->analyzeMeldCombinationList($handTileList);
        if ($handMeldCombinationList->isEmpty()) {
            return WinResult::createNotWin();
        }

        // 2. handMeldList's List -> subTargetList -> subResultList
        $subResultSelector = function (MeldList $handMeldList) use ($target) {
            $subTarget = $target->toSubTarget($handMeldList);
            return $this->analyzeSubTarget($subTarget);
        };
        $subResultList = (new ArrayList())->fromSelect($handMeldCombinationList, $subResultSelector);

        /**
         * 3. merge subResults into final result:
         *
         * best subResult     = subResult with highest yaku and fuCount
         * final yakuList     = best subResult.yakuList
         * final fuCount      = best subResult.fuCount
         * final winState     = best subResult.winState + handle furiten
         * todo waiting-but-not-win case
         */

        /** @var WinSubResult $targetSubResult */
        $targetSubResult = $subResultList->getMax(WinSubResult::getComparator());

        $finalYakuList = $targetSubResult->getYakuList();
        $finalFuCount = $targetSubResult->getFuCount();

        $finalWinState = $targetSubResult->getWinState();
        if ($finalWinState->isTrueWin()) {
            $waitingTileList = $this->getWaitingAnalyzer()->analyzePublic(
                $target->getPublicHand(), $target->getDeclaredMeldList()
            );
            $isFuritenFalseWin = $this->isFuritenFalseWin($target, $waitingTileList);
            if ($isFuritenFalseWin) {
                $finalWinState = WinState::create(WinState::FURITEN_FALSE_WIN);
            }
        }

        $finalResult = new WinResult($finalWinState, $finalYakuList, $finalFuCount);
        return $finalResult;
    }

    /**
     * Find WinSubResult.
     * Note that furiten is not considered in this phase for performance.
     * @param WinSubTarget $subTarget
     * @return WinSubResult
     */
    function analyzeSubTarget(WinSubTarget $subTarget) {
        // case1: not win
        $tileSeries = $this->getTileSeriesAnalyzer()->analyzeTileSeries($subTarget->getAllMeldList());
        if (!$tileSeries->isExist()) {
            return new WinSubResult(WinState::create(WinState::NOT_WIN), new YakuItemList(), 0);
        }

        // case2: no yaku false win
        $yakuList = $this->getYakuAnalyzer()->analyzeYakuList($subTarget);
        if ($yakuList->count() == 0) {
            return new WinSubResult(WinState::create(WinState::NO_YAKU_FALSE_WIN), new YakuItemList(), 0);
        }

        // case3: win by self or win by other
        $winState = WinState::getWinBySelfOrOther($subTarget->isPrivatePhase());

        $waitingType = $tileSeries->getWaitingType($subTarget->getAllMeldList(), $subTarget->getTileOfTargetTile(), $subTarget->getDeclaredMeldList());
        $fuCountTarget = new FuCountTarget($subTarget, $yakuList, $waitingType);
        $fuCountResult = FuCountAnalyzer::create()->getResult($fuCountTarget);
        $fuCount = $fuCountResult->getTotalFuCount();

        return new WinSubResult($winState, $yakuList, $fuCount);
    }

    /**
     * Precondition: match other win conditions. If not, return value may be confused.
     * @param WinTarget $target
     * @param TileList $waitingTileList
     * @return bool
     */
    function isFuritenFalseWin(WinTarget $target, TileList $waitingTileList) {
        // furiten has no effect on win-by-self
        if ($target->isPrivatePhase()) {
            return false;
        }

        $finalNgList = new TileList();
        $openHistory = $target->getOpenHistory();

        // ng case 1: self discarded tiles
        $selfNgList = $openHistory->getSelf($target->getSelfWind());
        $finalNgList->concat($selfNgList);

        // ng case 2: all other player's opened tiles since
        if ($target->isReach()) { // ng case 2: since self reach
            $fromTurn = $target->getReachTurn();
        } else { // ng case 3: since last turn where self discarded
            $globalTurn = $target->getGlobalTurn();
            if ($globalTurn == 1) {
                $fromTurn = 1;
            } else {
                $targetSelfWind = $target->getSelfWind();
                $currentSelfWind = $target->getCurrentPlayer()->getTileArea()->getPlayerWind()->getWindTile();
                $selfTurnPassed = $targetSelfWind->getWindOffsetFrom($currentSelfWind) <= 0;
                $fromTurn = $selfTurnPassed ? $globalTurn : $globalTurn - 1;
            }// todo more simpler logic
        }
        $excludedLastTile = true; // remember to exclude current target tile
        $otherNgList = $openHistory->getOther($target->getSelfWind(), $fromTurn, $target->getSelfWind(), $excludedLastTile);
        $finalNgList->concat($otherNgList);

        $isFuriten = $finalNgList->isAny(function (Tile $ngTile) use ($waitingTileList) {
            return $waitingTileList->valueExist($ngTile);
        });
        return $isFuriten;
    }
}
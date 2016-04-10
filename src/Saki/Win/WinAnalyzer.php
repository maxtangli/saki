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
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer($yakuSet->getTileSeriesList()->toArray());
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
            $isFuritenFalseWin = $this->isFuriten($target, $waitingTileList);
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
    function isFuriten(WinTarget $target, TileList $waitingTileList) {
        /**
         * design note: as a function since its not too complex,
         * extract class if dynamic furiten rule setting is required
         */

        // furiten has no effect on win-by-self
        if ($target->isPrivatePhase()) {
            return false;
        }

        $openHistory = $target->getOpenHistory();
        $isNgTile = function (Tile $ngTile) use ($waitingTileList) {
            return $waitingTileList->valueExist($ngTile);
        };
        $myPlayerWind = $target->getPlayerWind();

        // open furiten: self open TileList contains target tile
        $selfOpenList = $openHistory->getSelf($myPlayerWind);
        if ($selfOpenList->any($isNgTile)) {
            return true;
        }

        // reach furiten: other open TileList since self reach contains target tile
        $reachStatus = $target->getReachStatus();
        if ($reachStatus->isReach()) { // ng case 2: since self reach
            $reachRoundTurn = $reachStatus->getReachRoundTurn();
            $otherOpenListSinceReach = $openHistory->getOther($myPlayerWind, $reachRoundTurn);
            if ($otherOpenListSinceReach->any($isNgTile)) {
                return true;
            }
        }

        // turn furiten: other open TileList since self last open
        $lastOpenRoundTurn = $openHistory->getLastOpenOrFalse($myPlayerWind);
        if ($lastOpenRoundTurn !== false) {
            // design note: not introduce NullObject of RoundTurn here since it's seldom until now
            $otherOpenListSinceLastOpen = $openHistory->getOther($myPlayerWind, $lastOpenRoundTurn);
            if ($otherOpenListSinceLastOpen->any($isNgTile)) {
                return true;
            }
        }

        return false;
    }
}
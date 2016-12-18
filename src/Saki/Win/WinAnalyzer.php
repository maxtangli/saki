<?php
namespace Saki\Win;

use Saki\Game\Meld\MeldList;
use Saki\Game\Meld\MeldListAnalyzer;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\ChowMeldType;
use Saki\Game\Meld\ThirteenOrphanMeldType;
use Saki\Game\Meld\PungMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Fu\FuAnalyzer;
use Saki\Win\Fu\FuTarget;
use Saki\Win\Series\SeriesAnalyzer;
use Saki\Win\Waiting\WaitingAnalyzer;
use Saki\Win\Yaku\YakuAnalyzer;
use Saki\Win\Yaku\YakuItemList;
use Saki\Win\Yaku\YakuSet;

/**
 * Analyze WinResult for a given player.
 * @package Saki\Win
 */
class WinAnalyzer {
    private $yakuAnalyzer;
    private $seriesAnalyzer;
    private $waitingAnalyzer;
    private $handMeldListAnalyzer;

    /**
     * @param YakuSet $yakuSet
     */
    function __construct(YakuSet $yakuSet) {
        $this->yakuAnalyzer = new YakuAnalyzer($yakuSet);
        $this->seriesAnalyzer = new SeriesAnalyzer($yakuSet->getSeriesList()->toArray());
        $this->waitingAnalyzer = new WaitingAnalyzer(
            $this->seriesAnalyzer
        );

        $handMeldTypes = [
            ChowMeldType::create(),
            PungMeldType::create(),
            PairMeldType::create(),
            ThirteenOrphanMeldType::create(),
        ];
        $this->handMeldListAnalyzer = new MeldListAnalyzer($handMeldTypes);
    }

    /**
     * @return YakuAnalyzer
     */
    function getYakuAnalyzer() {
        return $this->yakuAnalyzer;
    }

    /**
     * @return SeriesAnalyzer
     */
    function getSeriesAnalyzer() {
        return $this->seriesAnalyzer;
    }

    /**
     * @return WaitingAnalyzer
     */
    function getWaitingAnalyzer() {
        return $this->waitingAnalyzer;
    }

    /**
     * @return MeldListAnalyzer
     */
    function getHandMeldListAnalyzer() {
        return $this->handMeldListAnalyzer;
    }

    /**
     * Find all possible WinSubResults and merge them into a final WinResult.
     * @param WinTarget $target
     * @return WinReport
     */
    function analyze(WinTarget $target) {
        $hand = $target->getHand();
        
        // 1. handTileList target -> handMeldList's List
        $handTileList = $hand->getPrivate();
        $handMeldListList = $this->getHandMeldListAnalyzer()
            ->analyzeMeldListList($handTileList);
        if ($handMeldListList->isEmpty()) {
            return WinReport::createNotWin($target->getActor());
        }

        // 2. handMeldList's List -> subTargetList -> subResultList
        $subResultSelector = function (MeldList $handMeldList) use ($target) {
            $subTarget = $target->toSubTarget($handMeldList);
            return $this->analyzeSub($subTarget);
        };
        $subResultList = (new ArrayList())->fromSelect($handMeldListList, $subResultSelector);

        /**
         * 3. merge subResults into final result:
         *
         * best subResult     = subResult with highest yaku and fu
         * final yakuList     = best subResult.yakuList
         * final fu      = best subResult.fu
         * final winState     = best subResult.winState + handle furiten
         * todo add waiting-but-not-win case?
         */

        /** @var WinSubReport $targetSubResult */
        $targetSubResult = $subResultList->getMax(WinSubReport::getPrioritySelector());

        $finalYakuList = $targetSubResult->getYakuItemList();
        $finalFu = $targetSubResult->getFu();

        $finalWinState = $targetSubResult->getWinState();
        if ($finalWinState->isTrueWin()) {
            $waitingTileList = $this->getWaitingAnalyzer()->analyzePublic(
                $hand->getPublic(), $hand->getMelded()
            );
            $isFuritenFalseWin = $this->isFuriten($target, $waitingTileList);
            if ($isFuritenFalseWin) {
                $finalWinState = WinState::create(WinState::FURITEN_FALSE_WIN);
            }
        }

        $finalResult = new WinReport($target->getActor(), $finalWinState, $finalYakuList, $finalFu);
        return $finalResult;
    }

    /**
     * Find WinSubResult.
     * Note that furiten is not considered in this phase for performance.
     * @param WinSubTarget $subTarget
     * @return WinSubReport
     */
    function analyzeSub(WinSubTarget $subTarget) {
        $actor = $subTarget->getActor();

        // case1: not win
        $series = $this->getSeriesAnalyzer()->analyzeSeries($subTarget->getAllMeldList());
        if (!$series->isExist()) {
            return new WinSubReport($actor, WinState::create(WinState::NOT_WIN), new YakuItemList(), 0);
        }

        // case2: no yaku false win
        $yakuList = $this->getYakuAnalyzer()->analyzeYakuList($subTarget);
        if ($yakuList->count() == 0) {
            return new WinSubReport($actor, WinState::create(WinState::NO_YAKU_FALSE_WIN), new YakuItemList(), 0);
        }

        // case3: win by self or win by other
        $winState = WinState::getTsumoOrOther($subTarget->getPhase()->isPrivate());

        $waitingType = $series->getWaitingType($subTarget->getSubHand());
        $fuTarget = new FuTarget($subTarget, $yakuList, $waitingType);
        $fuResult = FuAnalyzer::create()->getResult($fuTarget);
        $fu = $fuResult->getTotalFu();

        return new WinSubReport($actor, $winState, $yakuList, $fu);
    }

    /**
     * Precondition: match other win conditions. If not, return value may be confused.
     * @param WinTarget $target
     * @param TileList $waitingTileList
     * @return bool
     */
    function isFuriten(WinTarget $target, TileList $waitingTileList) {
        /**
         * design note: implemented as a function since it's not so complex,
         * extract class if dynamic furiten rule setting is required.
         */

        // A player who is furiten, can still win on a self-drawn tile
        if ($target->getPhase()->isPrivate()) {
            return false;
        }

        $openHistory = $target->getRound()->getTurnHolder()->getOpenHistory();
        $isNgTile = function (Tile $ngTile) use ($waitingTileList) {
            return $waitingTileList->valueExist($ngTile);
        };
        $mySeatWind = $target->getActor();

        // open furiten: self open TileList contains target tile
        $selfOpenList = $openHistory->getSelfOpen($mySeatWind);
        if ($selfOpenList->any($isNgTile)) {
            return true;
        }

        // reach furiten: other open TileList since self reach contains target tile
        $riichiStatus = $target->getRiichiStatus();
        if ($riichiStatus->isRiichi()) { // ng case 2: since self reach
            $riichiTurn = $riichiStatus->getRiichiTurn();
            $otherOpenListSinceRiichi = $openHistory->getOtherOpen($mySeatWind, $riichiTurn);
            if ($otherOpenListSinceRiichi->any($isNgTile)) {
                return true;
            }
        }

        // temporary furiten: other open TileList since self last open
        $lastOpenTurn = $openHistory->getLastOpenTurnOrFalse($mySeatWind);
        if ($lastOpenTurn !== false) {
            // design note: not introduce NullObject of Turn here since it's seldom until now
            $otherOpenListSinceLastOpen = $openHistory->getOtherOpen($mySeatWind, $lastOpenTurn);
            if ($otherOpenListSinceLastOpen->any($isNgTile)) {
                return true;
            }
        }

        return false;
    }
}
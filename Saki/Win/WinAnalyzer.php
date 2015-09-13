<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;
use Saki\Win\Fu\FuCountAnalyzer;
use Saki\Win\Fu\FuCountTarget;
use Saki\Win\Yaku\YakuAnalyzer;
use Saki\Win\Yaku\YakuList;

class WinAnalyzer {
    private $yakuAnalyzer;
    private $tileSeriesAnalyzer;
    private $waitingAnalyzer;

    function __construct() {
        $this->yakuAnalyzer = new YakuAnalyzer();
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
        $handTileList = $target->getHandTileSortedList();
        $handMeldTypes = [
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
            PairMeldType::getInstance(),
        ];
        $meldCompositions = $analyzer->analyzeMeldCompositions($handTileList, $handMeldTypes);
        if (empty($meldCompositions)) {
            return new WinResult(WinState::getInstance(WinState::NOT_WIN), new YakuList([], $target->isExposed()), 0, new TileSortedList([]));
        }

        // get winResult[] of each subTarget
        $subTargets = array_map(function (MeldList $meldList) use ($target) {
            return $target->toSubTarget($meldList);
        }, $meldCompositions);
        $subResults = $this->analyzeSubTargets($subTargets);

        /*
         * merge subResults into final result todo
         *
         * final yakuList/fuCount: max subResult.xx
         * final winState: handle furiten and waiting
         * final waitingTiles: waitingAnalyzer->analyzePublicPhaseWaitingTiles()
         */

        // get best winSubResult
        $l = new ArrayLikeObject($subResults);
        /** @var WinSubResult $targetSubResult */
        $targetSubResult = $l->getMax(WinSubResult::getComparator());
        $finalWinState = $targetSubResult->getWinState();

        // handle furiten
        if ($targetSubResult->getWinState()->isTrueWin()) {
            // todo bug
            $publicHandTileList = $target->getHandTileSortedList(false);
            if ($target->isPrivatePhase()) {
                $publicHandTileList->removeByValue($target->getWinTile());
            }
            $waitingTileList = $this->getWaitingAnalyzer()->analyzePublicPhaseHandWaitingTileList(
                $publicHandTileList, $target->getDeclaredMeldList()
            );

            // todo more detailed
            $isFuriten = false;
            if ($isFuriten) {
                $finalWinState = WinState::getInstance(WinState::FURITEN_FALSE_WIN);
            }
        }

        // final winResult
        $result = new WinResult($finalWinState, $targetSubResult->getYakuList(), $targetSubResult->getFuCount());
        return $result;
    }

    /**
     * exist to support code hinting
     * @param WinSubTarget[] $subTargets
     * @return WinSubResult[]
     */
    protected function analyzeSubTargets(array $subTargets) {
        $subResults = [];
        foreach ($subTargets as $subTarget) {
            $result = $this->analyzeSubTarget($subTarget);
            $subResults[] = $result;
        }
        return $subResults;
    }

    /**
     * note that waiting/furiten winState is not considered in this phase.
     * @param WinSubTarget $subTarget
     * @return WinSubResult
     */
    function analyzeSubTarget(WinSubTarget $subTarget) {
        $tileSeries = $this->getTileSeriesAnalyzer()->analyzeTileSeries($subTarget->getAllMeldList());
        if (!$tileSeries->exist()) {
            return new WinSubResult(WinState::getInstance(WinState::NOT_WIN), new YakuList([], $subTarget->isExposed()), 0);
        }

        $yakuList = $this->getYakuAnalyzer()->analyzeYakuList($subTarget);
        if ($yakuList->count() == 0) {
            return new WinSubResult(WinState::getInstance(WinState::NO_YAKU_FALSE_WIN), new YakuList([], $subTarget->isExposed()), 0);
        }

        $winStateValue = $subTarget->isPrivatePhase() ? WinState::WIN_BY_SELF : WinState::WIN_BY_OTHER;
        $winState = WinState::getInstance($winStateValue);

        $waitingType = $tileSeries->getWaitingType($subTarget->getAllMeldList(), $subTarget->getWinTile(), $subTarget->getDeclaredMeldList());
        $fuCountTarget = new FuCountTarget($subTarget, $yakuList, $waitingType);
        $fuCountResult = FuCountAnalyzer::getInstance()->getResult($fuCountTarget);
        $fuCount = $fuCountResult->getTotalFuCount();

        return new WinSubResult($winState, $yakuList, $fuCount);
    }

    protected function isFuritenFalseWin(WinTarget $target) {
        if ($target->isPrivatePhase()) {
            return false;
        }

        $waitingTiles = []; // todo

        $selfDiscardedTiles = $target->getDiscardedTileList()->toArray();
        if ($target->isReach()) {
            $otherTiles = []; // todo all other player's discarded tiles since reach
        } else {
            $otherTiles = []; // todo other player's tiles except in recent turn
        }

        $ngTiles = array_merge($selfDiscardedTiles, $otherTiles);
        $ngTileList = new TileList($ngTiles);
        $isDiscardedTileFalseWin = $ngTileList->any(function (Tile $ngTile) use ($waitingTiles) {
            return in_array($ngTile, $waitingTiles);
        });
        return $isDiscardedTileFalseWin;
    }
}


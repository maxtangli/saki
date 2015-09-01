<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Util\Utils;
use Saki\Win\Fu\FuCountAnalyzer;
use Saki\Win\Fu\FuCountTarget;
use Saki\Win\Yaku\YakuAnalyzer;
use Saki\Win\Yaku\YakuList;

class WinAnalyzer {
    private $yakuAnalyzer;
    private $tileSeriesAnalyzer;

    function __construct() {
        $this->yakuAnalyzer = new YakuAnalyzer();
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer();
    }

    function getYakuAnalyzer() {
        return $this->yakuAnalyzer;
    }

    function getTileSeriesAnalyzer() {
        return $this->tileSeriesAnalyzer;
    }

    /**
     * find all possible result and return the highest yaku-count ones.
     * @param WinTarget $target
     * @return WinAnalyzerResult
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
            return new WinAnalyzerResult(WinState::getInstance(WinState::NOT_WIN), new YakuList([], $target->isExposed()), 0);
        }

        // get analyzerResult[]
        $subTargets = array_map(function (MeldList $meldList) use ($target) {
            return $target->toSubTarget($meldList);
        }, $meldCompositions);
        $results = $this->analyzeSubTargets($subTargets);

        // return where yakuCount is max
        $isExposed = $target->isExposed();
        $result = Utils::array_max($results, function (WinAnalyzerResult $result) use ($isExposed) {
            return $result->getYakuList()->getFanCount($isExposed);
        });
        return $result;
    }

    /**
     * @param WinSubTarget[] $subTargets
     * @return WinAnalyzerResult[]
     */
    function analyzeSubTargets(array $subTargets) {
        $results = [];
        foreach ($subTargets as $subTarget) {
            $result = $this->analyzeSubTarget($subTarget);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param WinSubTarget $subTarget
     * @return WinAnalyzerResult
     */
    function analyzeSubTarget(WinSubTarget $subTarget) {
        /*
         * reach: isReach , 4winSetAnd1Pair or other winTiles
         * other yaku: has yaku means is wintile / clear
         *
         * win state
         * - not win tiles: win tiles not exist
         * - discarded win tile: win tiles exist but pinfu discarded
         * - no yaku: win tiles exist but yaku count = 0
         * - win: win tiles exist and yaku count > 0
         */

        $waitingType = $this->getTileSeriesAnalyzer()->analyzeWaitingType($subTarget->getAllMeldList(), $subTarget->getWinTile());
        if ($waitingType->exist()) {
            $yakuList = $this->getYakuAnalyzer()->analyzeYakuList($subTarget);
            if ($yakuList->count() == 0) {
                $winState = WinState::getInstance(WinState::NO_YAKU_FALSE_WIN);
            } else {
                if ($this->isDiscaredTileFalseWin($subTarget)) {
                    $winState = WinState::getInstance(WinState::DISCARDED_TILE_FALSE_WIN);
                } else {
                    $winStateValue = $subTarget->isPrivatePhase() ? WinState::WIN_BY_SELF : WinState::WIN_BY_OTHER;
                    $winState = WinState::getInstance($winStateValue);
                }
            }
        } else {
            $yakuList = new YakuList([], $subTarget->isExposed());
            $winState = WinState::getInstance(WinState::NOT_WIN);
        }

        $fuCountTarget = new FuCountTarget($subTarget, $yakuList, $waitingType);
        $fuCountResult = FuCountAnalyzer::getInstance()->getResult($fuCountTarget);
        $fuCount = $fuCountResult->getTotalFuCount();

        $result = new WinAnalyzerResult($winState, $yakuList, $fuCount);
        return $result;
    }

    protected function isDiscaredTileFalseWin(WinSubTarget $subTarget) {
        return false; // todo
    }
}


<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\MeldTypeAnalyzer;
use Saki\Util\Utils;

class WinAnalyzer {
    private $yakus;

    static function getDefaultYakus() {
        return [
            // 1 fan
            ReachYaku::getInstance(),
            RedValueTilesYaku::getInstance(),
            WhiteValueTilesYaku::getInstance(),
            GreenValueTilesYaku::getInstance(),
            SelfWindValueTilesYaku::getInstance(),
            RoundWindValueTilesYaku::getInstance(),
            AllSimplesYaku::getInstance(),
            AllRunsYaku::getInstance(),
            // yaku man
            FourConcealedTriplesYaku::getInstance(),
            // w yaku man
            FourConcealedTriplesOnePairWaitingYaku::getInstance(),
        ];
    }

    function __construct(array $yakus = null) {
        $this->yakus = $yakus !== null ? $yakus : static::getDefaultYakus();
    }

    function getYakus() {
        return $this->yakus;
    }

    /**
     * @param WinAnalyzerTarget $target
     * @return WinAnalyzerResult
     */
    function analyzeTarget(WinAnalyzerTarget $target) {
        // handTiles target -> handMelds[] subTarget
        $analyzer = new MeldCompositionsAnalyzer();
        $meldTypes = MeldTypeAnalyzer::getDefaultCandidateMeldTypes();
        $meldCompositions = $analyzer->analyzeMeldCompositions($target->getHandTileSortedList(), $meldTypes);
        if (empty($meldCompositions)) {
            return new WinAnalyzerResult(WinState::getNotWinTilesInstance(), new YakuList([], $target->isExposed()), 0);
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
     * @param WinAnalyzerSubTarget[] $subTargets
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
     * @param WinAnalyzerSubTarget $subTarget
     * @return WinAnalyzerResult
     */
    function analyzeSubTarget(WinAnalyzerSubTarget $subTarget) {
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

        $yakuList = new YakuList([], $subTarget->isExposed());
        if ($this->isWinTiles($subTarget)) {
            foreach ($this->yakus as $yaku) {
                if ($yaku->existIn($subTarget)) {
                    $yakuList->push($yaku); // winAnalyzerResult onChange hook: remove mutually-excluded yaku. or removeExcludedMethod.
                }
            }
            $yakuList->normalize();

            if ($yakuList->count() == 0) {
                $winState = WinState::getNoYakuInstance();
            } else {
                if ($this->isDiscaredWinTile($subTarget)) {
                    $winState = WinState::getDiscardedWinTileInstance();
                } else {
                    $winState = WinState::getWinInstance();
                }
            }
        } else {
            $winState = WinState::getNotWinTilesInstance();
        }

        $result = new WinAnalyzerResult($winState, $yakuList, $subTarget->getFuCount());
        return $result;
    }

    protected function isWinTiles(WinAnalyzerSubTarget $subTarget) {
        return $subTarget->is4WinSetAnd1Pair(); // todo
    }

    protected function isDiscaredWinTile(WinAnalyzerSubTarget $subTarget) {
        return false; // todo
    }

    function isWaiting(WinAnalyzerTarget $target) {
        return false; // todo
    }
}
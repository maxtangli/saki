<?php
namespace Saki\Yaku;

class YakuAnalyzer {
    private $yakus;

    static function getDefaultYakus() {
        return [
            ReachYaku::getInstance(),
            RedValueTilesYaku::getInstance(),
            WhiteValueTilesYaku::getInstance(),
            GreenValueTilesYaku::getInstance(),
            SelfWindValueTilesYaku::getInstance(),
            RoundWindValueTilesYaku::getInstance(),
            AllSimplesYaku::getInstance(),
            AllRunsYaku::getInstance(),
        ];
    }

    function __construct(array $yakus = null) {
        $this->yakus = $yakus !== null ? $yakus : static::getDefaultYakus();
    }

    function getYakus() {
        return $this->yakus;
    }

    /**
     * @param YakuAnalyzerTarget $target
     * @return YakuAnalyzerResult
     */
    function analyzeTarget(YakuAnalyzerTarget $target) {
        // handTiles target -> handMelds[] subTarget

        // get analyzerResult[]

        // return where yakuCount is max
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * @param YakuAnalyzerSubTarget[] $subTargets
     * @return YakuAnalyzerResult[]
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
     * @param YakuAnalyzerSubTarget $subTarget
     * @return YakuAnalyzerResult
     */
    function analyzeSubTarget(YakuAnalyzerSubTarget $subTarget) {
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

        $yakuList = new YakuList([]);
        if ($this->isWinTiles($subTarget)) {
            foreach ($this->yakus as $yaku) {
                if ($yaku->existIn($subTarget)) {
                    $yakuList->push($yaku); // yakuList onChange hook: remove mutually-excluded yaku. or removeExcludedMethod.
                }
            }
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

        $result = new YakuAnalyzerResult($winState, $yakuList);
        return $result;
    }

    function isWinTiles(YakuAnalyzerSubTarget $subTarget) {
        return $subTarget->is4WinSetAnd1Pair(); // todo
    }

    function isDiscaredWinTile(YakuAnalyzerSubTarget $subTarget) {
        return false; // todo
    }
}
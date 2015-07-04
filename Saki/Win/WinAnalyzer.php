<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\MeldTypeAnalyzer;
use Saki\Util\Utils;
use Saki\Yaku\AllRunsYaku;
use Saki\Yaku\AllSimplesYaku;
use Saki\Yaku\GreenValueTilesYaku;
use Saki\Yaku\ReachYaku;
use Saki\Yaku\RedValueTilesYaku;
use Saki\Yaku\RoundWindValueTilesYaku;
use Saki\Yaku\SelfWindValueTilesYaku;
use Saki\Yaku\WhiteValueTilesYaku;
use Saki\Yaku\YakuList;

class WinAnalyzer {
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
     * @param WinAnalyzerTarget $target
     * @return WinAnalyzerResult
     */
    function analyzeTarget(WinAnalyzerTarget $target) {
        // handTiles target -> handMelds[] subTarget
        $analyzer = new MeldCompositionsAnalyzer();
        $meldTypes = MeldTypeAnalyzer::getDefaultCandidateMeldTypes();
        $meldCompositions = $analyzer->analyzeMeldCompositions($target->getHandTileSortedList(), $meldTypes);
        if (empty($meldCompositions)) {
            return new WinAnalyzerResult(WinState::getNotWinTilesInstance(), new YakuList([], $target->isExposed()));
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

        $result = new WinAnalyzerResult($winState, $yakuList);
        return $result;
    }

    function isWinTiles(WinAnalyzerSubTarget $subTarget) {
        return $subTarget->is4WinSetAnd1Pair(); // todo
    }

    function isDiscaredWinTile(WinAnalyzerSubTarget $subTarget) {
        return false; // todo
    }
}
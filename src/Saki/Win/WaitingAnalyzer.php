<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldListAnalyzer;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\TileList;

/**
 * Analyze waiting tiles for a given player.
 *
 * Algorithm
 * - 18 tiles, series N, waitingTiles = merge (remove one tile -> 17 case)
 * - 18 tiles, series Y, waitingTiles without winTile = remove winTile -> 17 case
 * - 17 tiles, waitingTiles = algorithm(17 tiles)
 *
 * Used in
 * - reachable: 18 tiles, selfTile is in FutureWaitingList's discard
 * - exhaustiveDraw: each player 17 tiles, isWaiting?
 * - furiten: private 18-1 or public 17 tiles, is waitingTiles contains exclude tiles?
 * @package Saki\Win
 */
class WaitingAnalyzer {
    private $meldListAnalyzer;
    private $seriesAnalyzer;

    function __construct(SeriesAnalyzer $seriesAnalyzer) {
        $meldTypes = [
            RunMeldType::create(), TripleMeldType::create(),
            PairMeldType::create(),
            WeakRunMeldType::create(), WeakPairMeldType::create(),
        ];
        $this->meldListAnalyzer = new MeldListAnalyzer($meldTypes, 1);
        $this->seriesAnalyzer = $seriesAnalyzer;
    }

    /**
     * @return MeldListAnalyzer
     */
    function getMeldListAnalyzer() {
        return $this->meldListAnalyzer;
    }

    /**
     * @return SeriesAnalyzer
     */
    function getSeriesAnalyzer() {
        return $this->seriesAnalyzer;
    }

    /**
     * Analyze waiting tiles for a given player in private phase.
     *
     * Used in: ableReach.
     * @param TileList $private
     * @param MeldList $declare
     * @return FutureWaitingList
     */
    function analyzePrivate(TileList $private, MeldList $declare) {
        // todo validate private
        $futureWaitingList = new FutureWaitingList(); // todo more simpler possible?

        // discard each tile and test if remained handTiles has publicPhaseWaitingTiles
        $uniqueTiles = array_unique($private->toArray());
        $public = new TileList();
        foreach ($uniqueTiles as $discard) { // 7ms a loop
            $public->fromSelect($private)->remove($discard);
            $waiting = $this->analyzePublic($public, $declare);
            if ($waiting->count() > 0) {
                $futureWaitingList->insertLast(
                    new FutureWaiting($discard, $waiting)
                );
            }
        }

        return $futureWaitingList;
    }

    /**
     * Analyze waiting tiles for a given player in public phase.
     *
     * Used in: exhaustiveDraw, furiten.
     * @param TileList $public public hand
     * @param MeldList $declare
     * @return TileList unique sorted waiting tile list
     */
    function analyzePublic(TileList $public, MeldList $declare) {
        // todo validate public
        // break public into possible MeldLists
        // where each MeldList contains at most 1 WeakMeldType
        $orderedHand = $public->getCopy()->orderByTileID();
        $setListList = $this->getMeldListAnalyzer()
            ->analyzeMeldListList($orderedHand);

        // collect each MeldList's waiting as final waiting
        $waiting = new TileList();
        foreach ($setListList as $setList) {
            $waiting->concat(
                $this->getSetListWaiting($setList, $declare, $waiting)
            );
        }
        return $waiting->orderByTileID();
        /**
         * An alternative simple but slow algorithm,
         * which is replaced by current one.
         *
         * waiting = []
         * for each unique tile in tileSet
         *  private = public + tile
         *  all = meld analyze(private)
         *  series = series analyze(all)
         *  waiting[] = series.waitingTiles(targetTile = tile)
         * finalWaiting = waiting.distinct().orderBy()
         */
    }

    /**
     * @param MeldList $setList
     * @param MeldList $declare
     * @param TileList $ignore
     * @return TileList
     */
    protected function getSetListWaiting(MeldList $setList, MeldList $declare, TileList $ignore) {
        $waiting = new TileList();
        $currentIgnore = $ignore->getCopy();
        foreach ($this->getSourceList($setList) as $source) {
            $sourceWaiting = $this->getSourceWaiting($setList, $declare, $currentIgnore, $source);
            $waiting->concat($sourceWaiting);
            $currentIgnore->concat($sourceWaiting);
        }
        return $waiting;
    }

    /**
     * @param MeldList $setList
     * @return MeldList
     */
    protected function getSourceList(MeldList $setList) {
        // a setList's waiting tiles must come from:
        // - case1. two pairs'
        $pairList = $setList->toFiltered([PairMeldType::create()]);
        if (count($pairList) == 2) {
            return $pairList;
        }

        // - case2. one weakPair's or one weakRun's
        $weakList = $setList->toFiltered([WeakPairMeldType::create(), WeakRunMeldType::create()]);
        if (count($weakList) == 1) {
            return $weakList;
        }

        throw new \LogicException(
            sprintf('Invalid logic. $setList[%s].', $setList)
        );
    }

    /**
     * @param MeldList $setList
     * @param MeldList $declare
     * @param TileList $ignore
     * @param Meld $source
     * @return TileList
     */
    protected function getSourceWaiting(MeldList $setList, MeldList $declare, TileList $ignore, Meld $source) {
        $waiting = new TileList();

        $seriesAnalyzer = $this->getSeriesAnalyzer();
        foreach ($source->getWaiting() as $futureTile) {
            // ignore duplicated items to speedup
            if ($ignore->valueExist($futureTile)) {
                continue;
            }

            // with a $futureTile, source WeakMeld turns into a new Meld
            $sourceFuture = $source->toTargetMeld($futureTile);
            $all = $setList->getCopy()
                ->replace($source, $sourceFuture)
                ->concat($declare);

            // if with the new Meld any Series exist, $futureTile is a valid WaitingTile
            if ($seriesAnalyzer->analyzeSeries($all)->isExist()) {
                $waiting->insertLast($futureTile);
            }
        }

        return $waiting;
    }
}
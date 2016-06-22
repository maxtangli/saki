<?php
namespace Saki\Win\Waiting;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldListAnalyzer;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Meld\WeakThirteenOrphanMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Series\SeriesAnalyzer;

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
    private $publicMeldListAnalyzer;
    private $seriesAnalyzer;

    /**
     * @param SeriesAnalyzer $seriesAnalyzer
     */
    function __construct(SeriesAnalyzer $seriesAnalyzer) {
        $meldTypes = [
            RunMeldType::create(), TripleMeldType::create(),
            PairMeldType::create(),
            WeakRunMeldType::create(), WeakPairMeldType::create(),
            WeakThirteenOrphanMeldType::create(),
        ];
        $this->publicMeldListAnalyzer = new MeldListAnalyzer($meldTypes, 1);
        $this->seriesAnalyzer = $seriesAnalyzer;
    }

    /**
     * @return MeldListAnalyzer
     */
    function getPublicMeldListAnalyzer() {
        return $this->publicMeldListAnalyzer;
    }

    /**
     * @return SeriesAnalyzer
     */
    function getSeriesAnalyzer() {
        return $this->seriesAnalyzer;
    }

    /**
     * @param TileList $private
     * @param MeldList $melded
     * @param Tile $tile
     * @return bool
     */
    function isWaitingAfterDiscard(TileList $private, MeldList $melded, Tile $tile) {
        $futureWaitingList = $this->analyzePrivate($private, $melded);
        $isWaiting = $futureWaitingList->count() > 0;
        if (!$isWaiting) {
            return false;
        }

        $isValidTile = $futureWaitingList->discardExist($tile);
        if (!$isValidTile) {
            return false;
        }

        return true;
    }

    /**
     * Analyze waiting tiles for a given player in private phase.
     *
     * Used in: ableRiichi.
     * @param TileList $private
     * @param MeldList $melded
     * @return FutureWaitingList
     */
    function analyzePrivate(TileList $private, MeldList $melded) {
        // todo validate complete
        $valid = $private->getSize()->isPrivate();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $futureWaitingList = new FutureWaitingList();

        // discard each tile and test if remained public has waiting
        $uniqueTileList = $private->getCopy()->distinct(Tile::getEqual(true));
        $public = new TileList();
        foreach ($uniqueTileList as $discard) {
            $public->fromSelect($private)->remove($discard, Tile::getEqual(true));
            $waiting = $this->analyzePublic($public, $melded);
            if ($waiting->count() > 0) {
                $futureWaiting = new FutureWaiting($discard, $waiting);
                $futureWaitingList->insertLast($futureWaiting);
            }
        }

        return $futureWaitingList;
    }

    /**
     * Analyze waiting tiles for a given player in public phase.
     *
     * Used in: exhaustiveDraw, furiten.
     * @param TileList $public public hand
     * @param MeldList $melded
     * @return TileList unique sorted waiting tile list
     */
    function analyzePublic(TileList $public, MeldList $melded) {
        // todo validate public, complete
        // break public into possible MeldLists
        // where each MeldList contains at most 1 WeakMeldType
        $orderedHand = $public->getCopy()->orderByTileID();
        $setListList = $this->getPublicMeldListAnalyzer()
            ->analyzeMeldListList($orderedHand);

        // collect each MeldList's waiting as final waiting
        $waiting = new TileList();
        foreach ($setListList as $setList) {
            $waiting->concat(
                $this->getSetListWaiting($setList, $melded, $waiting)
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
     * @param MeldList $melded
     * @param TileList $ignore
     * @return TileList
     */
    protected function getSetListWaiting(MeldList $setList, MeldList $melded, TileList $ignore) {
        $waiting = new TileList();
        $currentIgnore = $ignore->getCopy();
        foreach ($this->getSourceList($setList) as $source) {
            $sourceWaiting = $this->getSourceWaiting($setList, $melded, $currentIgnore, $source);
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

        // - case1. two pairs' for 4+1 series
        $pairList = $setList->toFiltered([PairMeldType::create()]);
        if (count($pairList) == 2) {
            return $pairList;
        }

        // - case2. one weakPair's for seven-pairs series
        //       or one weakRun's for 4+1 series
        //       or one weakThirteenOrphan's for thirteen-orphan case
        $weakList = $setList->toFiltered([WeakPairMeldType::create(), WeakRunMeldType::create(), WeakThirteenOrphanMeldType::create()]);
        if (count($weakList) == 1) {
            return $weakList;
        }

        // otherwise: empty. todo strict prove
        return new MeldList();
//        throw new \LogicException(
//            sprintf('Invalid logic. $setList[%s].', $setList)
//        );
    }

    /**
     * @param MeldList $setList
     * @param MeldList $melded
     * @param TileList $ignore
     * @param Meld $source
     * @return TileList
     */
    protected function getSourceWaiting(MeldList $setList, MeldList $melded, TileList $ignore, Meld $source) {
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
                ->concat($melded);

            // if with the new Meld any Series exist, $futureTile is a valid WaitingTile
            if ($seriesAnalyzer->analyzeSeries($all)->isExist()) {
                $waiting->insertLast($futureTile);
            }
        }

        return $waiting;
    }
}
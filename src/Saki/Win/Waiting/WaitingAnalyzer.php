<?php
namespace Saki\Win\Waiting;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldList;
use Saki\Game\Meld\MeldListAnalyzer;
use Saki\Game\Meld\PairMeldType;
use Saki\Game\Meld\ChowMeldType;
use Saki\Game\Meld\PungMeldType;
use Saki\Game\Meld\WeakPairMeldType;
use Saki\Game\Meld\WeakChowMeldType;
use Saki\Game\Meld\WeakThirteenOrphanMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\Utils;
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
    private $analyzePrivateBuffer; // temp solution to speed up RiichiCommand provide

    /**
     * @param SeriesAnalyzer $seriesAnalyzer
     */
    function __construct(SeriesAnalyzer $seriesAnalyzer) {
        $meldTypes = [
            ChowMeldType::create(), PungMeldType::create(),
            PairMeldType::create(),
            WeakChowMeldType::create(), WeakPairMeldType::create(),
            WeakThirteenOrphanMeldType::create(),
        ];
        $this->publicMeldListAnalyzer = new MeldListAnalyzer($meldTypes, 1);
        $this->seriesAnalyzer = $seriesAnalyzer;
        $this->analyzePrivateBuffer = [];
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
     * Used in: Riichi
     * @param TileList $private
     * @param MeldList $melded
     * @param Tile $tile
     * @return bool
     */
    function isWaitingAfterDiscard(TileList $private, MeldList $melded, Tile $tile) {
        $key = $private->__toString() . '#' . $melded->__toString();
        $futureWaitingList = $this->analyzePrivateBuffer[$key]
            ?? $this->analyzePrivate($private, $melded);

        $isWaiting = $futureWaitingList->isNotEmpty();
        if (!$isWaiting) {
            return false;
        }

        $validTile = $futureWaitingList->discardExist($tile);
        if (!$validTile) {
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
        $uniqueTileList = $private->getCopy()->distinct();
        $public = new TileList();
        foreach ($uniqueTileList as $discard) {
            $public->fromSelect($private)->remove($discard, Tile::getPrioritySelector());
            $waiting = $this->analyzePublic($public, $melded);
            if ($waiting->isNotEmpty()) {
                $futureWaiting = new FutureWaiting($discard, $waiting);
                $futureWaitingList->insertLast($futureWaiting);
            }
        }

        $key = $private->__toString() . '#' . $melded->__toString();
        $this->analyzePrivateBuffer = [$key => $futureWaitingList];

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
//        foreach ($setListList as $setList) {
//            echo $setList."\n";
//        }

        // collect each MeldList's waiting as final waiting
        $waiting = new TileList();
        foreach ($setListList as $setList) {
//            MsTimer::create()->restart();
            $setListWaiting = $this->getSetListWaiting($setList, $melded, $waiting);
//            $ms = MsTimer::create()->restart();
//            echo "$setList: $ms.\n";

            $waiting->concat($setListWaiting);
        }

        return $waiting->distinct()->orderByTileID();
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
    private function getSetListWaiting(MeldList $setList, MeldList $melded, TileList $ignore) {
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
    private function getSourceList(MeldList $setList) {
        // a setList's waiting tiles must come from:

        // - case1. two pairs' for 4+1 series
        $pairList = $setList->toFiltered([PairMeldType::create()]);
        if (count($pairList) == 2) {
            return $pairList;
        }

        // - case2. one weakPair's for seven-pairs series
        //       or one weakChow's for 4+1 series
        //       or one weakThirteenOrphan's for thirteen-orphan case
        $weakList = $setList->toFiltered([WeakPairMeldType::create(), WeakChowMeldType::create(), WeakThirteenOrphanMeldType::create()]);
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
    private function getSourceWaiting(MeldList $setList, MeldList $melded, TileList $ignore, Meld $source) {
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
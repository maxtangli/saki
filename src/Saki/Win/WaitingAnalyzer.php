<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldCombinationAnalyzer;
use Saki\Meld\MeldList;
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
 * - 18 tiles, tileSeries N, waitingTiles = merge (remove one tile -> 17 case)
 * - 18 tiles, tileSeries Y, waitingTiles without winTile = remove winTile -> 17 case
 * - 17 tiles, waitingTiles = algorithm(17 tiles)
 *
 * Used in
 * - reachable: 18 tiles, selfTile is in FutureWaitingList's discard
 * - exhaustiveDraw: each player 17 tiles, isWaiting?
 * - furiten: private 18-1 or public 17 tiles, is waitingTiles contains exclude tiles?
 * @package Saki\Win
 */
class WaitingAnalyzer {
    private $meldCombinationAnalyzer;
    private $tileSeriesAnalyzer;

    function __construct(TileSeriesAnalyzer $tileSeriesAnalyzer) {
        $meldTypes = [
            RunMeldType::create(), TripleMeldType::create(),
            PairMeldType::create(),
            WeakRunMeldType::create(), WeakPairMeldType::create(),
        ];
        $this->meldCombinationAnalyzer = new MeldCombinationAnalyzer($meldTypes, 1);
        $this->tileSeriesAnalyzer = $tileSeriesAnalyzer;
    }

    /**
     * @return MeldCombinationAnalyzer
     */
    function getMeldCombinationAnalyzer() {
        return $this->meldCombinationAnalyzer;
    }

    /**
     * @return TileSeriesAnalyzer
     */
    function getTileSeriesAnalyzer() {
        return $this->tileSeriesAnalyzer;
    }

    /**
     * Analyze waiting tiles for a given player in private phase.
     *
     * Used in: ableReach.
     * @param TileList $handTileList private hand
     * @param MeldList $declaredMeldList
     * @return FutureWaitingList
     */
    function analyzePrivate(TileList $handTileList, MeldList $declaredMeldList) {
        if (!$handTileList->getHandSize()->isPrivate()) {
            throw new \InvalidArgumentException();
        }

        $futureWaitingList = new FutureWaitingList();

        // discard each tile and test if remained handTiles has publicPhaseWaitingTiles
        $uniqueHandTiles = array_unique($handTileList->toArray());
        $handTileListAfterDiscard = TileList::fromString('');
        foreach ($uniqueHandTiles as $discardedTile) { // 7ms a loop
            $handTileListAfterDiscard->fromArray($handTileList->toArray())->remove($discardedTile);

            $waitingTileList = $this->analyzePublic($handTileListAfterDiscard, $declaredMeldList);
            if ($waitingTileList->count() > 0) {
                $futureWaitingList->insertLast(new FutureWaiting($discardedTile, $waitingTileList));
            }
        }

        return $futureWaitingList;
    }

    /**
     * Analyze waiting tiles for a given player in public phase.
     *
     * Used in: exhaustiveDraw, furiten.
     * @param TileList $handTileList public hand
     * @param MeldList $declaredMeldList
     * @return TileList unique sorted waiting tile list
     */
    function analyzePublic(TileList $handTileList, MeldList $declaredMeldList) {
        if (!$handTileList->getHandSize()->isPublic()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid handTileList[%s] with count[%s] for analyzePublic().',
                    $handTileList, $handTileList->count()
                )
            );
        }
        $orderedHand = $handTileList->getCopy()->orderByTileID();

        /**
         * Algorithm
         *
         * todo
         */

        /**
         * An alternative simple but slow algorithm, which is replaced by current one.
         *
         * finalWaitingTiles = []
         * for each unique tile in tileSet
         *  private hand = public hand + tile
         *  tileSeries = TileSeriesAnalyzer.analyze(private hand, declareMeld)
         *  finalWaiting[] = tileSeries.waitingTiles(targetTile = tile)
         * finalWaitingTiles = finalWaitingTiles.unique().order()
         */

        // step1. break public hand into possible MeldLists which contains at most 1 WeakMeldType
        $handMeldCombinationList = $this->getMeldCombinationAnalyzer()
            ->analyzeMeldCombinationList($orderedHand);

        // step2. for each handMeldList,
        $tileSeriesAnalyzer = $this->getTileSeriesAnalyzer();
        $waitingTileList = new TileList();
        foreach ($handMeldCombinationList as $handMeldList) {
            // step2. given a handMeldList, if waitingTiles exist, they must come from melds that matches:
            //  case1. two pairs' waitingTiles
            //  case2. one weakPair or weakRun' waitingTiles
            // we name these melds as handSourceMeld
            /** @var MeldList $handMeldList */
            $handMeldList = $handMeldList;
            $handPairList = $handMeldList->toFiltered([PairMeldType::create()]);
            $handWeakPairOrWeakRunList = $handMeldList->toFiltered([WeakPairMeldType::create(), WeakRunMeldType::create()]);
            if (count($handPairList) == 2) {
                $handSourceMeldList = $handPairList;
            } elseif (count($handWeakPairOrWeakRunList) == 1) {
                $handSourceMeldList = $handWeakPairOrWeakRunList;
            } else {
                throw new \LogicException(
                    sprintf('Invalid implementation. $meldList[%s]', $handMeldList)
                );
            }

            foreach ($handSourceMeldList as $handSourceMeld) {
                // step3. given a handSourceMeld, get its waitingTiles, test each waitingTile if with it any TileSeries exist.
                //        take passed ones as finalWaitingTiles.
                /** @var Meld $handSourceMeld */
                $handSourceMeld = $handSourceMeld;
                $potentialWaitingTiles = $handSourceMeld->getWaitingTileList();
                foreach ($potentialWaitingTiles as $potentialWaitingTile) {
                    if ($waitingTileList->valueExist($potentialWaitingTile)) { // ignore duplicated items to speedup
                        continue;
                    }

                    $futureHandMeld = $handSourceMeld->toTargetMeld($potentialWaitingTile);
                    $futureAllMeldList = $handMeldList->getCopy()
                        ->replace($handSourceMeld, $futureHandMeld)
                        ->concat($declaredMeldList);

                    $tileSeries = $tileSeriesAnalyzer->analyzeTileSeries($futureAllMeldList);
                    if ($tileSeries->isExist()) {
                        $waitingTileList->insertLast($potentialWaitingTile);
                    }
                }
            }
        }

        $waitingTileList->orderByTileID();
        return $waitingTileList;
    }
}
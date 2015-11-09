<?php
namespace Saki\Win;

use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\TileSortedList;

/**
 * cases
 * - 18 tiles, tileSeries N, waitingTiles = merge (remove one tile -> 17 case)
 * - 18 tiles, tileSeries Y, waitingTiles without winTile = remove winTile -> 17 case
 * - 17 tiles, waitingTiles = algorithm(17 tiles)
 *
 * usage
 * - reachable: 18 tiles, selfTile is in FutureWaitingList's discard
 * - exhaustiveDraw: each player 17 tiles, isWaiting?
 * - furiten: private 18-1 or public 17 tiles, is waitingTiles contains exclude tiles?
 * @package Saki\Win
 */
class WaitingAnalyzer {
    private $meldCompositionsAnalyzer;

    function __construct() {
        $this->meldCompositionsAnalyzer = new MeldCompositionsAnalyzer();
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer(); // todo
    }

    /**
     * use case: ableReach.
     * @param TileSortedList $handTileList 18-tiles-style target.
     * @param MeldList $declaredMeldList
     * @return FutureWaitingList
     */
    function analyzePrivatePhaseFutureWaitingList(TileSortedList $handTileList, MeldList $declaredMeldList) {
        if (!$handTileList->isPrivateHandCount()) {
            throw new \InvalidArgumentException();
        }

        $futureWaitingList = new FutureWaitingList([]);

        // discard each tile and test if left-handTiles has publicPhaseWaitingTiles
        $uniqueHandTiles = array_unique($handTileList->toArray());
        $handTileListAfterDiscard = new TileSortedList([]);
        foreach ($uniqueHandTiles as $discardedTile) { // 0.06s a loop
            $handTileListAfterDiscard->setInnerArray($handTileList->toArray());
            $handTileListAfterDiscard->removeByValue($discardedTile);

            $waitingTileList = $this->analyzePublicPhaseHandWaitingTileList($handTileListAfterDiscard, $declaredMeldList);
            if ($waitingTileList->count() > 0) {
                $futureWaitingList->push(new FutureWaiting($discardedTile, $waitingTileList));
            }
        }

        return $futureWaitingList;
    }

    /**
     * use case: exhaustiveDraw, furiten.
     * @param TileSortedList $handTileList 17-like tile list
     * @param MeldList $declaredMeldList
     * @return TileSortedList unique waiting tile list
     */
    function analyzePublicPhaseHandWaitingTileList(TileSortedList $handTileList, MeldList $declaredMeldList) {
        if (!$handTileList->isPublicHandCount()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid handTileList[%s] with count[%s] for WaitingAnalyzer public phase analyze.', $handTileList, $handTileList->count())
            );
        }

        // step1. break 17-like tiles into MeldList include at most 1 WeakType
        $tileSeriesAnalyzer = $this->tileSeriesAnalyzer;
        $meldCompositionAnalyzer = $this->meldCompositionsAnalyzer;
        $meldTypes = [
            RunMeldType::getInstance(), TripleMeldType::getInstance(),
            PairMeldType::getInstance(),
            WeakRunMeldType::getInstance(), WeakPairMeldType::getInstance(),
        ];
        $handMeldLists = $meldCompositionAnalyzer->analyzeMeldCompositions($handTileList, $meldTypes, 1);

        $waitingTileList = new TileSortedList([]);
        foreach ($handMeldLists as $handMeldList) {
            // step2. given a handMeldList, if waitingTiles exist, they must come from melds that matches:
            //  case1. two pairs' waitingTiles
            //  case2. one weakPair or weakRun' waitingTiles
            // we name these melds as handSourceMeld
            $handPairList = $handMeldList->toFilteredTypesMeldList([PairMeldType::getInstance()]);
            $handWeakPairOrWeakRunList = $handMeldList->toFilteredTypesMeldList([WeakPairMeldType::getInstance(), WeakRunMeldType::getInstance()]);
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
                //        Take passed ones as finalWaitingTiles.
                $potentialWaitingTiles = $handSourceMeld->getWaitingTiles();
                foreach ($potentialWaitingTiles as $potentialWaitingTile) {
                    if ($waitingTileList->valueExist($potentialWaitingTile)) { // ignore duplicate items to speedup
                        continue;
                    }

                    $futureHandMeld = $handSourceMeld->toTargetMeld($potentialWaitingTile);
                    $futureAllMeldList = new MeldList($handMeldList->toArray());
                    $futureAllMeldList->replaceByValue($handSourceMeld, $futureHandMeld);
                    $futureAllMeldList->merge($declaredMeldList);

                    $tileSeries = $tileSeriesAnalyzer->analyzeTileSeries($futureAllMeldList);
                    if ($tileSeries->exist()) {
                        $waitingTileList->push($potentialWaitingTile);
                    }
                }
            }
        }

        return $waitingTileList;
    }
}
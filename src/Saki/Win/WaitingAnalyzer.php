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
    private $tileSeriesAnalyzer;

    function __construct() {
        $this->meldCompositionsAnalyzer = new MeldCombinationAnalyzer();
        $this->tileSeriesAnalyzer = new TileSeriesAnalyzer(); // todo pass by arguments
    }

    function getMeldCompositionsAnalyzer() {
        return $this->meldCompositionsAnalyzer;
    }

    function getTileSeriesAnalyzer() {
        return $this->tileSeriesAnalyzer;
    }

    /**
     * use case: ableReach.
     * @param TileList $handTileList 18-tiles-style target.
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
     * use case: exhaustiveDraw, furiten.
     * @param TileList $tileList 17-like tile list
     * @param MeldList $declaredMeldList
     * @return TileList unique sorted waiting tile list
     */
    function analyzePublic(TileList $tileList, MeldList $declaredMeldList) {
        if (!$tileList->getHandSize()->isPublic()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid handTileList[%s] with count[%s] for WaitingAnalyzer public phase analyze.', $handTileList, $handTileList->count())
            );
        }
        $handTileList = $tileList->getCopy()->orderByTileID();

        // step1. break 17-like tiles into MeldList which include at most 1 WeakType
        $meldTypes = [
            RunMeldType::getInstance(), TripleMeldType::getInstance(),
            PairMeldType::getInstance(),
            WeakRunMeldType::getInstance(), WeakPairMeldType::getInstance(),
        ];
        $handMeldCompositionList = $this->getMeldCompositionsAnalyzer()->analyzeMeldCombinationList($handTileList, $meldTypes, 1);
        $handMeldLists = $handMeldCompositionList->toArray();
        
        // step2. for each handMeldList,
        $tileSeriesAnalyzer = $this->getTileSeriesAnalyzer();
        $waitingTileList = new TileList();
        foreach ($handMeldLists as $handMeldList) {
            // step2. given a handMeldList, if waitingTiles exist, they must come from melds that matches:
            //  case1. two pairs' waitingTiles
            //  case2. one weakPair or weakRun' waitingTiles
            // we name these melds as handSourceMeld
            $handPairList = $handMeldList->toFiltered([PairMeldType::getInstance()]);
            $handWeakPairOrWeakRunList = $handMeldList->toFiltered([WeakPairMeldType::getInstance(), WeakRunMeldType::getInstance()]);
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
<?php

namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;

class MeldCompositionsAnalyzer {

    static $debug_time_cost = 0;

    /**
     * @param TileList|\Saki\Tile\TileSortedList $tileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $exposed
     * @return MeldList[]
     */
    function analyzeMeldCompositions(TileList $tileList, array $meldTypes, $allowPureWeakCount = 0, $exposed = false) {
        $debug_time_start = microtime(true);
        $tileSortedList = $tileList instanceof TileSortedList ? $tileList : new TileSortedList($tileList->toArray());

        $meldLists = $this->analyzeMeldCompositionsImpl($tileSortedList, $meldTypes, $allowPureWeakCount, $exposed);

        $debug_time_end = microtime(true);
        self::$debug_time_cost += ($debug_time_end - $debug_time_start);
        //echo 'meld analyzer: '.self::$debug_time_cost."\n";

        return $meldLists;
    }

    /**
     * @param \Saki\Tile\TileSortedList $tileSortedList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $exposed
     * @return MeldList[]
     */
    protected function analyzeMeldCompositionsImpl(TileSortedList $tileSortedList, array $meldTypes, $allowPureWeakCount, $exposed) {
        /*
         * meldList(tiles) = all merge(a valid meld from begin, meldList(other tiles))
         */

        $allMeldLists = [];
        foreach ($meldTypes as $meldType) {
            $isPureWeak = $meldType->getWinSetType()->isPureWeak();
            if ($isPureWeak && $allowPureWeakCount <= 0) {
                continue;
            }

            $possibleCuts = $this->getPossibleCuts($tileSortedList, $meldType);
            if (!empty($possibleCuts)) { // with first tile, success to construct a meld by given meldType
                foreach($possibleCuts as list($beginTileSortedList, $remainTileSortedList)) {
                    $firstMeld = new Meld($beginTileSortedList, $meldType, $exposed);
                    if (count($remainTileSortedList) > 0) {
                        $nextAllowPureWeakCount = $isPureWeak ? $allowPureWeakCount - 1 : $allowPureWeakCount;
                        $thisMeldLists = $this->analyzeMeldCompositionsImpl($remainTileSortedList, $meldTypes, $nextAllowPureWeakCount, $exposed);
                        if (count($thisMeldLists) > 0) { // with first meld, success to turn all remain tiles into melds
                            foreach ($thisMeldLists as $meldList) {
                                $meldList->unShift($firstMeld);
                            }
                            $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                        } else { // with first meld, failed to turn all remain tiles into melds
                            continue;
                        }
                    } else {
                        $thisMeldLists = [new MeldList([$firstMeld])];
                        $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                    }
                }
            } else { // with first tile, failed to construct a meld by given meldType
                continue;
            }
        }

        return $allMeldLists;
    }

    /**
     * @param TileSortedList $tileSortedList
     * @param MeldType $meldType
     * @return TileSortedList[]
     */
    protected function getPossibleCuts(TileSortedList $tileSortedList, MeldType $meldType) {
        if ($tileSortedList->count() == 0) {
            return [];
        }

        $result = [];
        $meldTileSortedLists = $meldType->getPossibleTileSortedLists($tileSortedList[0]);
        foreach($meldTileSortedLists as $meldTileSortedList) {
            $meldTiles = $meldTileSortedList->toArray();
            if ($tileSortedList->valueExist($meldTiles)) {
                $remainTileSortedList = new TileSortedList($tileSortedList->toArray());
                $remainTileSortedList->removeByValue($meldTiles);
                $result[] = [$meldTileSortedList, $remainTileSortedList];
            }
        }
        return $result;
    }
}
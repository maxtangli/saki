<?php

namespace Saki\Meld;

use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;

class MeldCompositionsAnalyzer {

    static $debug_time_cost = 0;

    /**
     * @param TileList|\Saki\Tile\TileSortedList $tileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @return MeldList[]
     */
    function analyzeMeldCompositions(TileList $tileList, array $meldTypes, $allowPureWeakCount = 0, $debug_isFirstCall = true) {
        if ($debug_isFirstCall) {
            $start = microtime(true);
        }

        /*
         * meldList(tiles) = all merge(a valid meld from begin, meldList(other tiles))
         */
        $actualTileList = $tileList instanceof TileSortedList ? $tileList : new TileSortedList($tileList->toArray());

        $allMeldLists = [];
        foreach ($meldTypes as $meldType) {
            $isPureWeak = $meldType == WeakRunMeldType::getInstance() || $meldType == SingleMeldType::getInstance(); // todo refactor
            if ($isPureWeak && $allowPureWeakCount <= 0) {
                continue;
            }

            list($beginTileList, $remainTileList) = $actualTileList->getCutInTwoTileLists($meldType->getTileCount());
            if ($meldType->valid($beginTileList)) {
                $firstMeld = new Meld($beginTileList, $meldType);
                if (count($remainTileList) > 0) {
                    $nextAllowPureWeakCount = $isPureWeak ? $allowPureWeakCount - 1 : $allowPureWeakCount;
                    $thisMeldLists = $this->analyzeMeldCompositions($remainTileList, $meldTypes, $nextAllowPureWeakCount, false);
                    if (count($thisMeldLists) > 0) {
                        foreach ($thisMeldLists as $meldList) {
                            $meldList->insert($firstMeld, 0);
                        }
                        $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                    }
                } else {
                    $thisMeldLists = [new MeldList([$firstMeld])];
                    $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
                }
            }
        }

        if ($debug_isFirstCall) {
            $end = microtime(true);
            self::$debug_time_cost += ($end - $start);
            //echo 'meld analyzer: '.self::$debug_time_cost."\n";
        }
        return $allMeldLists;
    }
}
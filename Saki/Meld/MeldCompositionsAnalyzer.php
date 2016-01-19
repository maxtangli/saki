<?php

namespace Saki\Meld;

use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Util\MsTimer;

class MeldCompositionsAnalyzer {

    /**
     * @param TileList $tileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $toConcealed
     * @return MeldList[]
     */
    function analyzeMeldCompositions(TileList $tileList, array $meldTypes, $allowPureWeakCount = 0, $toConcealed = true) {
        $tileSortedList = $tileList instanceof TileSortedList ? $tileList : new TileSortedList($tileList->toArray(), false);
        $meldLists = $this->analyzeMeldCompositionsImpl($tileSortedList, $meldTypes, $allowPureWeakCount, $toConcealed);
        return $meldLists;
    }

    /**
     * @param TileList $tileSortedList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $toConcealed
     * @return MeldList[]
     */
    protected function analyzeMeldCompositionsImpl(TileList $tileSortedList, array $meldTypes, $allowPureWeakCount, $toConcealed) {
        /*
         * meldList(tiles) = all merge(a valid meld from begin, meldList(other tiles))
         */

        $allMeldLists = [];
        foreach ($meldTypes as $meldType) {
            $isPureWeak = $meldType->getWinSetType()->isPureWeak();
            if ($isPureWeak && $allowPureWeakCount <= 0) {
                continue;
            }

//            MsTimer::getInstance()->restart();
            $possibleCuts = $this->getPossibleCuts($tileSortedList, $meldType);
            if (empty($possibleCuts)) { // with first tile, failed to construct a meld by given meldType
                continue;
            }
//            echo "get possible cuts: ";MsTimer::getInstance()->restartWithDump();

            // with first tile, success to construct a meld by given meldType
            foreach ($possibleCuts as list($beginTileSortedList, $remainTileSortedList)) {
                $firstMeld = new Meld($beginTileSortedList, $meldType, $toConcealed);

                if ($remainTileSortedList->isEmpty()) { // with first meld, no remain tiles exist
                    $thisMeldLists = [new MeldList([$firstMeld])];
                } else {
                    $nextAllowPureWeakCount = $isPureWeak ? $allowPureWeakCount - 1 : $allowPureWeakCount;
                    $thisMeldLists = $this->analyzeMeldCompositionsImpl($remainTileSortedList, $meldTypes, $nextAllowPureWeakCount, $toConcealed);
                    if (empty($thisMeldLists)) { // with first meld, failed to turn all remain tiles into melds
                        continue;
                    }

                    // with first meld, success to turn all remain tiles into melds
                    foreach ($thisMeldLists as $meldList) {
                        $meldList->unShift($firstMeld);
                    }
                }

                $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
            }

//            echo "foreach loop: ";MsTimer::getInstance()->restartWithDump();echo "\n";
        }
        return $allMeldLists;
    }

    /**
     * @param TileList $tileList
     * @param MeldType $beginMeldType
     * @return TileList[] [$meldTileList, $remainTileSortedList]
     */
    protected function getPossibleCuts(TileList $tileList, MeldType $beginMeldType) {
        if ($tileList->isEmpty()) {
            return [];
        }

        $result = [];
        $meldTileLists = $beginMeldType->getPossibleTileLists($tileList[0]);
        foreach ($meldTileLists as $meldTileList) {
            $meldTiles = $meldTileList->toArray();
            if ($tileList->valueExist($meldTiles)) {
                $remainTileSortedList = (new TileList($tileList->toArray()))
                    ->removeByValue($meldTiles);
                $result[] = [$meldTileList, $remainTileSortedList];
            }
        }
        return $result;
    }
}
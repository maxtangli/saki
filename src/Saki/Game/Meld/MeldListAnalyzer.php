<?php

namespace Saki\Game\Meld;

use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * Analyze all possible meld combinations for a given TileList and conditions.
 * @package Saki\Game\Meld
 */
class MeldListAnalyzer {
    private $meldTypes;
    private $allowPureWeakCount;

    /**
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     */
    function __construct(array $meldTypes, int $allowPureWeakCount = 0) {
        $this->meldTypes = $meldTypes;
        $this->allowPureWeakCount = $allowPureWeakCount;
    }

    /**
     * @return MeldType[]
     */
    function getMeldTypes() {
        return $this->meldTypes;
    }

    /**
     * @return int
     */
    function getAllowPureWeakCount() {
        return $this->allowPureWeakCount;
    }

    /**
     * @param TileList $tileList
     * @return ArrayList Return an ArrayList of MeldList which is a melds-combination of $tileList.
     */
    function analyzeMeldListList(TileList $tileList) {
        $orderedTileList = $tileList->getCopy()->orderByTileID();
        $meldLists = $this->analyzeMeldTypesImpl(
            $orderedTileList,
            $this->getMeldTypes(),
            $this->getAllowPureWeakCount()
        );
        $compositionList = new ArrayList($meldLists);
        return $compositionList;
    }

    /**
     * @param TileList $orderedTileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @return MeldList[]
     */
    protected function analyzeMeldTypesImpl(TileList $orderedTileList, array $meldTypes,
                                            int $allowPureWeakCount) {
        /**
         * algorithm overview:
         *
         * meldLists(tiles, meldTypes)
         * = meldLists(first tile + remain tiles, meldTypes)
         * = [
         *      meldLists(first tile + remain tiles, meldType.1)
         *      ...
         *      meldLists(first tile + remain tiles, meldType.n)
         *   ]
         * = [
         *      meldList(valid meld.1 of meldType.1 begin with first tile, meldLists(remain tiles, meldTypes))
         *      ...
         *      meldList(valid meld.n of meldType.1 begin with first tile, meldLists(remain tiles, meldTypes))
         *
         *      ...
         *
         *      meldList(valid meld.1 of meldType.n begin with first tile, meldLists(remain tiles, meldTypes))
         *      ...
         *      meldList(valid meld.n of meldType.n begin with first tile, meldLists(remain tiles, meldTypes))
         *   ]
         */

        $allMeldLists = [];
        foreach ($meldTypes as $meldType) {
            $thisMeldLists = $this->analyzeMeldTypeImpl(
                $orderedTileList, $meldTypes, $allowPureWeakCount, $meldType
            );
            $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
        }
        return $allMeldLists;
    }

    /**
     * @param TileList $orderedTileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param MeldType $meldType
     * @return MeldList[]
     */
    protected function analyzeMeldTypeImpl(TileList $orderedTileList, array $meldTypes,
                                           int $allowPureWeakCount, MeldType $meldType) {
        $isPureWeak = $meldType->getWinSetType()->isPureWeak();
        if ($isPureWeak && $allowPureWeakCount <= 0) {
            return [];
        }

        $possibleCuts = $meldType->getPossibleCuts($orderedTileList);
        if (empty($possibleCuts)) {
            return [];
        }

        $allMeldLists = [];
        foreach ($possibleCuts as list($beginTileList, $remainTileList)) {
            // success to find a valid meld begin with first tile under $meldType
            $thisMeldLists = $this->analyzeCutImpl(
                $meldTypes, $allowPureWeakCount,
                $meldType, $beginTileList, $remainTileList
            );
            $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
        }
        return $allMeldLists;
    }

    /**
     * @param array $meldTypes
     * @param int $allowPureWeakCount
     * @param MeldType $meldType
     * @param TileList $beginTileOrderedList
     * @param TileList $remainTileOrderedList
     * @return array|MeldList[]
     */
    protected function analyzeCutImpl(array $meldTypes,
                                      int $allowPureWeakCount, MeldType $meldType,
                                      TileList $beginTileOrderedList, TileList $remainTileOrderedList) {
        $beginMeld = new Meld($beginTileOrderedList->toArray(), $meldType, true);

        if ($remainTileOrderedList->isEmpty()) {
            // with begin meld, no remain tiles exist thus success
            return [new MeldList([$beginMeld])];
        }

        // with begin, try to turn remain tiles into melds
        $nextAllowPureWeakCount = $beginMeld->getWinSetType()->isPureWeak()
            ? $allowPureWeakCount - 1
            : $allowPureWeakCount;
        $remainMeldLists = $this->analyzeMeldTypesImpl(
            $remainTileOrderedList, $meldTypes, $nextAllowPureWeakCount
        );

        if (empty($remainMeldLists)) {
            // with begin meld, failed to turn remain tiles into melds
            return [];
        }

        // with begin meld, success to turn remain tiles into melds
        $fullMeldLists = $remainMeldLists;
        foreach ($fullMeldLists as $meldList) {
            $meldList->insertFirst($beginMeld);
        }
        return $fullMeldLists;
    }
}
<?php

namespace Saki\Meld;

use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * Analyze all possible meld combinations for a given TileList and conditions.
 * @package Saki\Meld
 */
class MeldCombinationAnalyzer {
    private $meldTypes;
    private $allowPureWeakCount;
    private $toConcealed;

    function __construct(array $meldTypes, int $allowPureWeakCount = 0, bool $toConcealed = true) {
        $this->meldTypes = $meldTypes;
        $this->allowPureWeakCount = $allowPureWeakCount;
        $this->toConcealed = $toConcealed;
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
     * @return bool
     */
    function getToConcealed() {
        return $this->toConcealed;
    }

    /**
     * @param TileList $tileList
     * @return ArrayList Return an ArrayList of MeldList, where each MeldList means a meld combination.
     */
    function analyzeMeldCombinationList(TileList $tileList) {
        $orderedTileList = $tileList->getCopy()->orderByTileID();
        $meldLists = $this->analyzeMeldTypesImpl(
            $orderedTileList,
            $this->getMeldTypes(),
            $this->getAllowPureWeakCount(),
            $this->getToConcealed()
        );
        $compositionList = new ArrayList($meldLists);
        return $compositionList;
    }

    /**
     * @param TileList $orderedTileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $toConcealed
     * @return MeldList[]
     */
    protected function analyzeMeldTypesImpl(TileList $orderedTileList, array $meldTypes,
                                            int $allowPureWeakCount, bool $toConcealed) {
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
                $orderedTileList, $meldTypes, $allowPureWeakCount, $toConcealed,
                $meldType
            );
            $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
        }
        return $allMeldLists;
    }

    /**
     * @param TileList $orderedTileList
     * @param MeldType[] $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $toConcealed
     * @param MeldType $meldType
     * @return MeldList[]
     */
    protected function analyzeMeldTypeImpl(TileList $orderedTileList, array $meldTypes,
                                           int $allowPureWeakCount, bool $toConcealed,
                                           MeldType $meldType) {
        $isPureWeak = $meldType->getWinSetType()->isPureWeak();
        if ($isPureWeak && $allowPureWeakCount <= 0) {
            return [];
        }

        $possibleCuts = $this->getPossibleCuts($orderedTileList, $meldType);
        if (empty($possibleCuts)) {
            return [];
        }

        $allMeldLists = [];
        foreach ($possibleCuts as list($beginTileList, $remainTileList)) {
            // success to find a valid meld begin with first tile under $meldType
            $thisMeldLists = $this->analyzeCutImpl(
                $orderedTileList, $meldTypes, $allowPureWeakCount, $toConcealed,
                $meldType,
                $beginTileList, $remainTileList
            );
            $allMeldLists = array_merge($allMeldLists, $thisMeldLists);
        }
        return $allMeldLists;
    }

    /**
     * @param TileList $orderedTileList
     * @param array $meldTypes
     * @param int $allowPureWeakCount
     * @param bool $toConcealed
     * @param MeldType $meldType
     * @param TileList $beginTileOrderedList
     * @param TileList $remainTileOrderedList
     * @return array|MeldList[]
     */
    protected function analyzeCutImpl(TileList $orderedTileList, array $meldTypes,
                                      int $allowPureWeakCount, bool $toConcealed,
                                      MeldType $meldType,
                                      TileList $beginTileOrderedList, TileList $remainTileOrderedList) {
        $beginMeld = new Meld($beginTileOrderedList->toArray(), $meldType, $toConcealed);

        if ($remainTileOrderedList->isEmpty()) {
            // with begin meld, no remain tiles exist thus success
            return [new MeldList([$beginMeld])];
        }

        // with begin, try to turn remain tiles into melds
        $nextAllowPureWeakCount = $beginMeld->getWinSetType()->isPureWeak() ?
            $allowPureWeakCount - 1 : $allowPureWeakCount;
        $remainMeldLists = $this->analyzeMeldTypesImpl(
            $remainTileOrderedList, $meldTypes, $nextAllowPureWeakCount, $toConcealed
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

    /**
     * @param TileList $sourceTileList
     * @param MeldType $beginMeldType
     * @return TileList[] Returns [[$beginTileList, $remainTileList]...] if success, [] otherwise.
     *                    Note that WeakRun may return multiple possible cuts.
     */
    protected function getPossibleCuts(TileList $sourceTileList, MeldType $beginMeldType) {
        if ($sourceTileList->isEmpty()) {
            return [];
        }

        $meldTileLists = $beginMeldType->getPossibleTileLists($sourceTileList[0]);
        $accumulator = function (array $result, TileList $meldTileList) use ($sourceTileList) {
            $twoCut = $sourceTileList->toTwoCut($meldTileList->toArray());
            return $twoCut !== false ? array_merge($result, [$twoCut]) : $result;
        };
        return (new ArrayList($meldTileLists))->getAggregated([], $accumulator);
    }
}
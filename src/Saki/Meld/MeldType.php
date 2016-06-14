<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\ClassNameToString;
use Saki\Util\Singleton;

/**
 * A specific pattern for a not empty TileList.
 * @package Saki\Meld
 */
abstract class MeldType extends Singleton {
    use ClassNameToString;

    /**
     * @return int
     */
    abstract function getTileCount();

    /**
     * @param TileList $tileList
     * @return bool
     */
    final function valid(TileList $tileList) {
        return $this->validCount($tileList) && $this->validFaces($tileList);
    }

    /**
     * @param TileList $tileList
     * @return bool
     */
    final protected function validCount(TileList $tileList) {
        return count($tileList) == $this->getTileCount();
    }

    /**
     * @param TileList $validCountTileList
     * @return bool
     */
    abstract protected function validFaces(TileList $validCountTileList);

    /**
     * @param TileList $sourceTileList
     * @return TileList[] Returns [[$beginTileList, $remainTileList]...] if success, [] otherwise.
     *                    Note that WeakRun may return multiple possible cuts.
     */
    function getPossibleCuts(TileList $sourceTileList) {
        if ($sourceTileList->isEmpty()) {
            return [];
        }

        $meldTileLists = $this->getPossibleTileLists($sourceTileList[0]);
        $accumulator = function (array $result, TileList $meldTileList) use ($sourceTileList) {
            $twoCut = $sourceTileList->toTwoCut($meldTileList->toArray());
            return $twoCut !== false ? array_merge($result, [$twoCut]) : $result;
        };
        return (new ArrayList($meldTileLists))->getAggregated([], $accumulator);
    }

    /**
     * Used in: meld composition analyze.
     * @param Tile $firstTile
     * @return TileList[] possible ordered TileLists begin with $firstTile under this MeldType.
     */
    abstract protected function getPossibleTileLists(Tile $firstTile);

    /**
     * @param Tile $firstTile
     * @return TileList[]
     */
    final protected function getPossibleTileListsImplByRepeat(Tile $firstTile) {
        $tiles = array_fill(0, $this->getTileCount(), $firstTile);
        return [new TileList($tiles)];
    }

    /**
     * @return bool
     */
    function hasTargetMeldType() {
        return false;
    }

    /**
     * @return WinSetType
     */
    abstract function getWinSetType();
}


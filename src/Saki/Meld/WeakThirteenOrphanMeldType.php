<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * @package Saki\Meld
 */
class WeakThirteenOrphanMeldType extends WeakMeldType {
    /**
     * @return TileList
     */
    protected function getBaseTileList() {
        return TileList::fromString('19m19p19sESWNCPF');
    }

    //region MeldType impl
    function getTileCount() {
        return 13;
    }

    protected function validFaces(TileList $validCountTileList) {
        $uniqueTileList = $validCountTileList->toTileList()->distinct();
        return $uniqueTileList->isAllTermOrHonour()
        && $uniqueTileList->count() >= 12;
    }

    function getPossibleCuts(TileList $sourceTileList) {
        if (!$this->valid($sourceTileList)) {
            return [];
        }

        $twoCut = [$sourceTileList, new TileList()];
        return [$twoCut];
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        throw new \BadMethodCallException('removed since too slow: +1.6s for all tests.');
//        if ($firstTile != Tile::fromString('1m')) {
//            return [];
//        }
//
//        // baseTileList + anyone base - anyone base
//        $base = $this->getBaseTileList();
//        $resultSelector = function (Tile $remove, Tile $insert) use ($base) {
//            return $base->getCopy()
//                ->remove($remove)
//                ->insertLast($insert);
//        };
//        return (new ArrayList())->fromZipped($base, $base, $resultSelector)
//            ->toArray();
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::PURE_WEAK);
    }
    //endregion

    //region WeakMeldType impl
    function getTargetMeldType() {
        return ThirteenOrphanMeldType::create();
    }

    protected function getWaitingTileListImpl(TileList $validMeldTileList) {
        $uniqueTileList = $validMeldTileList->toTileList()->distinct();
        return $uniqueTileList->count() == 13 ?
            $uniqueTileList :
            $this->getBaseTileList()->remove($uniqueTileList->toArray());
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::create(WaitingType::ORPHAN_WAITING);
    }
    //endregion
}
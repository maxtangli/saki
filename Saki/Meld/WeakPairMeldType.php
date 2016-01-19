<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;
use Saki\Win\WaitingType;

class WeakPairMeldType extends WeakMeldType {
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        return true;
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return PairMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileSortedList $validMeldTileSortedList) {
        return [$validMeldTileSortedList[0]];
    }

    protected function getWaitingTypeImpl(TileSortedList $validMeldTileSortedList) {
        return WaitingType::getInstance(WaitingType::PAIR_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PURE_WEAK);
    }
}
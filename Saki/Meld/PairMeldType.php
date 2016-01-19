<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Win\WaitingType;

class PairMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        return $tileSortedList[0] == $tileSortedList[1];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return TripleMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileSortedList $validMeldTileSortedList) {
        return [$validMeldTileSortedList[0]];
    }

    protected function getWaitingTypeImpl(TileSortedList $validMeldTileSortedList) {
        return WaitingType::getInstance(WaitingType::TRIPLE_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PAIR);
    }
}
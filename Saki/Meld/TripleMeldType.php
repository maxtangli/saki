<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Win\WaitingType;

class TripleMeldType extends WeakMeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        return $tileSortedList[0] == $tileSortedList[1] && $tileSortedList[1] == $tileSortedList[2];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return QuadMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileSortedList $validMeldTileSortedList) {
        return [$validMeldTileSortedList[0]];
    }

    protected function getWaitingTypeImpl(TileSortedList $validMeldTileSortedList) {
        return WaitingType::getInstance(WaitingType::NOT_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::HAND_WIN_SET);
    }
}
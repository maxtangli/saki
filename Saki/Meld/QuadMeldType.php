<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class QuadMeldType extends MeldType {
    function getTileCount() {
        return 4;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        return $tileSortedList[0] == $tileSortedList[1] && $tileSortedList[1] == $tileSortedList[2] && $tileSortedList[2] == $tileSortedList[3];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::DECLARE_WIN_SET);
    }
}
<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class QuadMeldType extends MeldType {
    function getTileCount() {
        return 4;
    }

    protected function validFaces(TileList $validCountTileList) {
        return $validCountTileList[0] == $validCountTileList[1] && $validCountTileList[1] == $validCountTileList[2] && $validCountTileList[2] == $validCountTileList[3];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::DECLARE_WIN_SET);
    }
}
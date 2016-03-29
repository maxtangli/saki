<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class QuadMeldType extends MeldType {
    function getTileCount() {
        return 4;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1] && $tileList[1] == $tileList[2] && $tileList[2] == $tileList[3];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::DECLARE_WIN_SET);
    }
}
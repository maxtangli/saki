<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * @package Saki\Meld
 */
class QuadMeldType extends MeldType {
    //region MeldType impl
    function getTileCount() {
        return 4;
    }

    protected function validFaces(TileList $validCountTileList) {
        return $validCountTileList[0] == $validCountTileList[1] && $validCountTileList[1] == $validCountTileList[2] && $validCountTileList[2] == $validCountTileList[3];
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::DECLARE_WIN_SET);
    }
    //endregion
}
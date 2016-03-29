<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class TripleMeldType extends WeakMeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1] && $tileList[1] == $tileList[2];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return QuadMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $validMeldTileList) {
        return [$validMeldTileList[0]];
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::getInstance(WaitingType::NOT_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::HAND_WIN_SET);
    }
}
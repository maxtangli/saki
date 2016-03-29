<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class PairMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return TripleMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $validMeldTileList) {
        return [$validMeldTileList[0]];
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::getInstance(WaitingType::TRIPLE_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PAIR);
    }
}
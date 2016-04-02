<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class PairMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $validCountTileList) {
        return $validCountTileList[0] == $validCountTileList[1];
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return TripleMeldType::getInstance();
    }

    protected function getWaitingTileListImpl(TileList $validMeldTileList) {
        return new TileList([$validMeldTileList[0]]);
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::getInstance(WaitingType::TRIPLE_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PAIR);
    }
}
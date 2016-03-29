<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class WeakPairMeldType extends WeakMeldType {
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileList $tileList) {
        return true;
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return PairMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $validMeldTileList) {
        return [$validMeldTileList[0]];
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::getInstance(WaitingType::PAIR_WAITING);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PURE_WEAK);
    }
}
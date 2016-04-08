<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class WeakPairMeldType extends WeakMeldType {
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileList $validCountTileList) {
        return true;
    }

    function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return PairMeldType::create();
    }

    protected function getWaitingTileListImpl(TileList $validMeldTileList) {
        return new TileList([$validMeldTileList[0]]);
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::create(WaitingType::PAIR_WAITING);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::PURE_WEAK);
    }
}
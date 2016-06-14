<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * @package Saki\Meld
 */
class PairMeldType extends WeakMeldType {
    //region MeldType impl
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $validCountTileList) {
        return $validCountTileList[0] == $validCountTileList[1];
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return TripleMeldType::create();
    }
    //endregion

    //region WeakMeldType impl
    protected function getWaitingTileListImpl(TileList $validMeldTileList) {
        return new TileList([$validMeldTileList[0]]);
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::create(WaitingType::TRIPLE_WAITING);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::PAIR);
    }
    //endregion
}
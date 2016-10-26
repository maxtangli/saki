<?php
namespace Saki\Game\Meld;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * @package Saki\Game\Meld
 */
class WeakPairMeldType extends WeakMeldType {
    //region MeldType impl
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileList $validCountTileList) {
        return true;
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }
    //endregion

    //region WeakMeldType impl
    function getTargetMeldType() {
        return PairMeldType::create();
    }

    protected function getWaitingImpl(TileList $validMeldTileList) {
        return new TileList([$validMeldTileList[0]]);
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::create(WaitingType::PAIR_WAITING);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::PURE_WEAK);
    }
    //endregion
}
<?php
namespace Saki\Game\Meld;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * @package Saki\Game\Meld
 */
class PungMeldType extends WeakMeldType {
    //region MeldType impl
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $validCountTileList) {
        return $validCountTileList[0] == $validCountTileList[1] && $validCountTileList[1] == $validCountTileList[2];
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        return $this->getPossibleTileListsImplByRepeat($firstTile);
    }

    function getTargetMeldType() {
        return KongMeldType::create();
    }
    //endregion

    //region WeakMeldType impl
    protected function getWaitingImpl(TileList $validMeldTileList) {
        return new TileList([$validMeldTileList[0]]);
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        return WaitingType::create(WaitingType::NOT_WAITING);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::HAND_WIN_SET);
    }
    //endregion
}
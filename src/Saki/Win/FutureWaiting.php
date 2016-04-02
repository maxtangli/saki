<?php
namespace Saki\Win;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class FutureWaiting {
    private $discardedTile;
    private $waitingTileList;

    function __construct(Tile $discardedTile, TileList $waitingTileList) {
        $this->discardedTile = $discardedTile;
        $this->waitingTileList = $waitingTileList->getCopy()->orderByTileID();
    }

    function getDiscardedTile() {
        return $this->discardedTile;
    }

    function getWaitingTileList() {
        return $this->waitingTileList;
    }
}
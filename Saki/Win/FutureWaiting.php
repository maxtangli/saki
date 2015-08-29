<?php
namespace Saki\Win;

use Saki\Tile\Tile;

class FutureWaiting {
    private $discardedTile;
    private $waitingTileList;

    function __construct(Tile $discardedTile, WaitingTileList $waitingTileList) {
        $this->discardedTile = $discardedTile;
        $this->waitingTileList = $waitingTileList;
    }

    function getDiscardedTile() {
        return $this->discardedTile;
    }

    function getWaitingTileList() {
        return $this->waitingTileList;
    }
}
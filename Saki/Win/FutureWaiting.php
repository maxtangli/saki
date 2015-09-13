<?php
namespace Saki\Win;

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class FutureWaiting {
    private $discardedTile;
    private $waitingTileList;

    function __construct(Tile $discardedTile, TileSortedList $waitingTileList) {
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
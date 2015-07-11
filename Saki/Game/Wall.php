<?php
namespace Saki\Game;

use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;

class Wall {
    private $tileSet;
    private $deadWall;
    private $remainTileList;

    function __construct(TileSet $tileSet) {
        $valid = $tileSet->count() === 136;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileSet = $tileSet;
        $this->reset(false);
    }

    /**
     * set $currentTileList based on $baseTileList
     * @param bool $shuffle
     */
    function reset($shuffle = true) {
        $baseTileList = new TileList($this->getTileSet()->toArray());
        if ($shuffle) {
            $baseTileList->shuffle();
        }
        list($deadWallTileLists, $currentTileList) = $baseTileList->getCutInTwoTileLists(14);
        $this->deadWall = new DeadWall($deadWallTileLists);
        $this->remainTileList = $currentTileList;
    }

    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getRemainTileList()->__toString();
    }

    /**
     * @return TileSet
     */
    function getTileSet() {
        return $this->tileSet;
    }

    /**
     * @return DeadWall
     */
    function getDeadWall() {
        return $this->deadWall;
    }

    /**
     * @return TileList
     */
    function getRemainTileList() {
        return $this->remainTileList;
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        return $this->getRemainTileList()->count();
    }

    /**
     * @param int $n
     * @return \Saki\Tile\Tile|\Saki\Tile\Tile[]
     */
    function pop($n = 1) {
        return $this->getRemainTileList()->pop($n);
    }

    /**
     * @return \Saki\Tile\Tile
     */
    function shift() {
        return $this->getDeadWall()->shift();
    }
}
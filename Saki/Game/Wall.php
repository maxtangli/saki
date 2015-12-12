<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSet;
use Saki\Util\ArrayLikeObject;

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
        list($deadWallTileLists, $currentTileList) = $baseTileList->toTwoPart(14);
        $this->deadWall = new DeadWall($deadWallTileLists);
        $this->remainTileList = $currentTileList;
    }

    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getRemainTileList()->__toString();
    }

    function debugSetNextDrawTile(Tile $tile) {
        $this->getRemainTileList()->replaceByIndex($this->getRemainTileList()->count() - 1, $tile);
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
     * @param $n
     * @return Tile[]
     */
    function drawInit($n) {
        $tiles = $this->getRemainTileList()->getLastMany($n);
        $this->getRemainTileList()->pop($n);
        return $tiles;
    }

    /**
     * @return Tile
     */
    function draw() {
        $tile = $this->getRemainTileList()->getLast();
        $this->getRemainTileList()->pop();
        return $tile;
    }

    /**
     * @return Tile
     */
    function drawReplacement() {
        return $this->getDeadWall()->shift();
    }
}
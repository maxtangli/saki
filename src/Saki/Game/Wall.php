<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSet;

/**
 * @package Saki\Game
 */
class Wall {
    private $tileSet;
    private $deadWall;
    private $doraFacade;
    private $remainTileList;

    /**
     * @param TileSet $tileSet
     */
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
        $this->deadWall = new DeadWall($deadWallTileLists); // todo remove new
        $this->doraFacade = new DoraFacade($this->deadWall);
        $this->remainTileList = $currentTileList;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getRemainTileList()->__toString();
    }

    /**
     * @param Tile $tile
     */
    function debugSetNextDrawTile(Tile $tile) {
        $this->getRemainTileList()->replaceAt($this->getRemainTileList()->count() - 1, $tile);
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
     * @return DoraFacade
     */
    function getDoraFacade() {
        return $this->doraFacade;
    }

    /**
     * @return TileList
     */
    protected function getRemainTileList() {
        return $this->remainTileList;
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        return $this->getRemainTileList()->count();
    }

    /**
     * @param PlayerType $playerType
     * @return Tile[][] e.x. [E => [1s,2s...] ...]
     */
    function deal(PlayerType $playerType) {
        $result = [];
        foreach ([4, 4, 4, 1] as $drawTileCount) {
            foreach ($playerType->getSeatWindList() as $actor) {
                $tiles = $this->getRemainTileList()->getLastMany($drawTileCount);

                $i = $actor->__toString();
                $result[$i] = array_merge($result[$i] ?? [], $tiles);

                $this->getRemainTileList()->removeLast($drawTileCount);
            }
        }
        return $result;
    }

    /**
     * @return Tile
     */
    function draw() {
        $tile = $this->getRemainTileList()->getLast();
        $this->getRemainTileList()->removeLast();
        return $tile;
    }
}
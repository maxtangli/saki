<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;

/**
 * @package Saki\Game
 */
class Wall {
    private $tileSet;
    private $deadWall;
    private $doraFacade;
    /** @var TileList */
    private $remainTileList;

    /**
     * @param TileSet $tileSet
     * @param bool $shuffle
     */
    function __construct(TileSet $tileSet, bool $shuffle = true) {
        $valid = $tileSet->count() === 136;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileSet = $tileSet;

        list($deadWallTileLists, $currentTileList) = $this->generateTwoParts($shuffle);
        $this->deadWall = new DeadWall($deadWallTileLists);
        $this->doraFacade = new DoraFacade($this->deadWall);
        $this->remainTileList = $currentTileList;
    }

    /**
     * set $currentTileList based on $baseTileList
     * @param bool $shuffle
     */
    function reset($shuffle = true) {
        list($deadWallTileLists, $currentTileList) = $this->generateTwoParts($shuffle);
        $this->deadWall->reset($deadWallTileLists);
        $this->remainTileList = $currentTileList;
    }

    /**
     * @param bool $shuffle
     * @return TileList[] list($deadWallTileLists, $currentTileList)
     */
    protected function generateTwoParts(bool $shuffle = true) {
        $baseTileList = new TileList($this->getTileSet()->toArray());
        if ($shuffle) {
            $baseTileList->shuffle();
        }
        return $baseTileList->toTwoPart(14);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getRemainTileList()->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        $a = $this->getDeadWall()->toJson();
        $a['remainTileCount'] = $this->getRemainTileCount();
        return $a;
    }

    /**
     * @param Tile $tile
     */
    function debugSetNextDrawTile(Tile $tile) {
        $lastPos = $this->getRemainTileCount() - 1;
        $this->remainTileList->replaceAt($lastPos, $tile); // validate
    }
    
    function debugSetRemainTileCount(int $n) {
        $removeCount = $this->getRemainTileCount() - $n;
        $this->remainTileList->removeLast($removeCount); // validate
        
        if ($this->remainTileList->count() != $n) {
            throw new \LogicException();    
        }
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
     * @return bool
     */
    function isBottomOfTheSea() {
        return $this->getRemainTileCount() == 0;
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
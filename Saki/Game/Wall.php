<?php
namespace Saki\Game;

use Saki\Tile;
use Saki\TileList;
use Saki\TileType;
use Saki\Util\ArrayLikeObject;

class Wall {

    static function getStandardTileList() {
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEESSSSWWWWNNNNCCCCPPPPFFFF';
        return TileList::fromString($s);
    }

    private $baseTileReadonlyList;
    private $deadWall;
    private $remainTileList;

    function __construct(TileList $baseTileList) {
        $valid = $baseTileList->count() === 136;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $tiles = $baseTileList->toArray();
        $this->baseTileReadonlyList = new TileList($tiles, true);
        $this->init(false);
    }

    /**
     * set $currentTileList based on $baseTileList
     * @param bool $shuffle
     */
    function init($shuffle = true) {
        $baseTileList = new TileList($this->getBaseTileReadonlyList()->toArray());
        if ($shuffle) {
            $baseTileList->shuffle();
        }
        list($deadWallTileLists, $currentTileList) = $baseTileList->getCutInTwoTileLists(14);
        $this->deadWall = new DeadWall($deadWallTileLists);
        $this->remainTileList = $currentTileList;
    }

    function __toString() {
        return $this->getDeadWall()->__toString().','.$this->getRemainTileList()->__toString();
    }

    /**
     * @return TileList
     */
    function getBaseTileReadonlyList() {
        return $this->baseTileReadonlyList;
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
        return count($this->getRemainTileList());
    }

    /**
     * @return Tile
     */
    function pop() {
        return $this->getRemainTileList()->pop();
    }

    /**
     * @param int $n
     * @return Tile[]
     */
    function popMany($n) {
        return $this->getRemainTileList()->popMany($n);
    }

    /**
     * @return Tile
     */
    function shift() {
        return $this->getDeadWall()->shift();
    }
}
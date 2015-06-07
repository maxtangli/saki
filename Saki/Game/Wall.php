<?php
namespace Saki\Game;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Saki\Tile;
use Saki\TileList;
use Saki\Util\ArrayLikeObject;

class Wall extends ArrayLikeObject {

    static function getStandardTileList() {
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEEWWWWSSSSNNNNCCCCFFFFPPPP';
        return TileList::fromString($s);
    }

    private $baseTileReadonlyList;

    function __construct(TileList $baseTileList, TileList $currentTileList = null) {
        $tiles = $baseTileList->toArray();
        $this->baseTileReadonlyList = new TileList($tiles, true);
        $actualCurrentTiles = $currentTileList ? $currentTileList->toArray() : $tiles;
        parent::__construct($actualCurrentTiles);
    }

    function __toString() {
        return (new TileList($this->toArray()))->__toString();
    }

    function getBaseTileReadonlyList() {
        return $this->baseTileReadonlyList;
    }

    /**
     * set $currentTileList based on $baseTileList
     * @param bool $shuffle
     */
    function init($shuffle = true) {
        $newCurrentTiles = $this->getBaseTileReadonlyList()->toArray();
        if ($shuffle) {
            shuffle($newCurrentTiles);
        }
        $this->setInnerArray($newCurrentTiles);
    }

    /**
     * @return Tile
     */
    function pop() {
        list($tile) = $this->popMany(1);
        return $tile;
    }

    /**
     * @param int $n
     * @return Tile[]
     */
    function popMany($n) {
        $newCurrentTiles = $this->toArray();
        if (count($newCurrentTiles) < $n) {
            throw new \InvalidArgumentException();
        }

        $ret = [];
        for ($i = 0; $i < $n; ++$i) {
            $ret[] = array_pop($newCurrentTiles);
        }
        $this->setInnerArray($newCurrentTiles);
        return $ret;
    }

    /**
     * @return Tile
     */
    function shift() {
        $newCurrentTiles = $this->toArray();
        if (count($newCurrentTiles) < 1) {
            throw new \InvalidArgumentException();
        }

        $ret = array_shift($newCurrentTiles);
        $this->setInnerArray($newCurrentTiles);
        return $ret;
    }

    /**
     * @return Tile[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @return Tile
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }


}
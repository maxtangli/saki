<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Game\Wall
 */
class Stack {
    /** @var ArrayList [] or [$bottomStackTile] or [$topStackTile, $bottomStackTile] */
    private $stackTileList;

    function __construct() {
        $this->stackTileList = new ArrayList();
    }

    function init() {
        $this->stackTileList->removeAll();
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->stackTileList->__toString();
    }

    /**
     * @return array e.x. ['X', 'X'] or ['X', '2s'] or ['1s', '2s']
     */
    function toJson() {
        return [$this->getTop()->toJson(), $this->getBottom()->toJson()];
    }

    /**
     * @return StackTile
     */
    function getTop() {
        return $this->stackTileList->count() == 2
            ? $this->stackTileList->getFirst()
            : StackTile::createNull();
    }

    /**
     * @return StackTile
     */
    function getBottom() {
        return $this->stackTileList->count() >= 1
            ? $this->stackTileList->getLast()
            : StackTile::createNull();
    }

    /**
     * @return TileList
     */
    function toTileList() {
        $a = $this->stackTileList->toArray(Utils::getMethodCallback('getTile'));
        return new TileList($a);
    }

    /**
     * @return int
     */
    function getCount() {
        return $this->stackTileList->count();
    }

    /**
     * @return bool
     */
    function isEmpty() {
        return $this->stackTileList->isEmpty();
    }

    /**
     * @return bool
     */
    function isNotEmpty() {
        return $this->stackTileList->isNotEmpty();
    }

    /**
     * @param Tile[] $tileChunk
     */
    function setTileChunk(array $tileChunk) {
        $valid = (count($tileChunk) == 2);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $toStackTile = function (Tile $tile) {
            return new StackTile($tile);
        };
        $this->stackTileList->fromSelect(new TileList($tileChunk), $toStackTile);
    }

    /**
     * @param Tile $tile
     */
    function setNextPopTile(Tile $tile) {
        /** @var StackTile $stackTile */
        $stackTile = $this->stackTileList->getFirst();
        $stackTile->setTile($tile);
    }

    /**
     * @return Tile
     */
    function popTile() {
        /** @var StackTile $stackTile */
        $stackTile = $this->stackTileList->shift();
        return $stackTile->getTile();
    }
}
<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game\Wall
 */
class Stack {
    /** @var ArrayList [] or [$bottomTile] or [$topTile, $bottomTile] */
    private $tileList;

    function __construct() {
        $this->tileList = new TileList();
    }

    function init() {
        $this->tileList->removeAll();
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->tileList->__toString();
    }

    /**
     * @param bool $hide
     * @return array e.x. ['X', 'X'] or ['X', '2s'] or ['1s', '2s']
     */
    function toJson(bool $hide = false) {
        $json = $this->tileList->toJson($hide);
        while (count($json) < 2) {
            array_unshift($json, 'X');
        }
        return $json;
    }

    /**
     * @return ArrayList
     */
    function getTileList() {
        return $this->tileList;
    }

    /**
     * @return int
     */
    function getTopTile() {
        return $this->tileList->assertCount(2)->offsetGet(0);
    }

    /**
     * @return int
     */
    function getBottomTile() {
        return $this->tileList->assertCount(2)->offsetGet(1);
    }

    /**
     * @return int
     */
    function getCount() {
        return $this->tileList->count();
    }

    /**
     * @return bool
     */
    function isEmpty() {
        return $this->tileList->isEmpty();
    }

    /**
     * @param Tile[] $tileChunk
     */
    function setTileChunk(array $tileChunk) {
        $valid = (count($tileChunk) == 2);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileList->removeAll()
            ->insertLast($tileChunk);
    }

    /**
     * @param Tile $tile
     */
    function setNextPopTile(Tile $tile) {
        $this->tileList->replaceFirst($tile); // validate
    }

    /**
     * @return Tile
     */
    function popTile() {
        return $this->tileList->shift(); // validate
    }
}
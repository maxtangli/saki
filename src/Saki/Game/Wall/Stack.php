<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game\Wall
 */
class Stack {
    /** @var ArrayList [] or [$bottomTile] or [$bottomTile, $topTile] */
    private $tileList;

    function __construct() {
        $this->tileList = new ArrayList();
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

        // $tileChunk = [$tile0, $tile1]
        // $tileList = [$tile1, $tile0]
        $this->tileList->removeAll()
            ->insertLast($tileChunk[1])
            ->insertLast($tileChunk[0]);
    }

    /**
     * @param Tile $tile
     */
    function setNextPopTile(Tile $tile) {
        $this->tileList->replaceLast($tile); // validate
    }

    /**
     * @return Tile
     */
    function popTile() {
        return $this->tileList->pop(); // validate
    }
}
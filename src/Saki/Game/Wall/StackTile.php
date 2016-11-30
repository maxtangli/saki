<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\Tile;

/**
 * @package Saki\Game\Wall
 */
class StackTile {
    /**
     * @return StackTile
     */
    static function createNull() {
        $obj = new self(Tile::fromString('E'));
        $obj->tile = null;
        return $obj;
    }

    private $tile;
    private $opened;

    /**
     * @param Tile $tile
     * @param bool $opened
     */
    function __construct(Tile $tile, bool $opened = false) {
        $this->tile = $tile;
        $this->opened = $opened;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->toJson();
    }

    /**
     * @return string
     */
    function toJson() {
        if ($this->exist()) {
            return $this->isOpened() ? $this->getTile()->__toString() : 'O';
        } else {
            return 'X';
        }
    }

    /**
     * @return bool
     */
    function exist() {
        return $this->tile !== null;
    }

    function assertExist() {
        if (!$this->exist()) {
            throw new \LogicException();
        }
    }

    /**
     * @return Tile
     */
    function getTile() {
        $this->assertExist();
        return $this->tile;
    }

    /**
     * @param Tile $tile
     */
    function setTile(Tile $tile) {
        $this->assertExist();
        $this->tile = $tile;
    }

    /**
     * @return boolean
     */
    function isOpened() {
        $this->assertExist();
        return $this->opened;
    }

    function open() {
        $this->assertExist();
        // ignore validation
        $this->opened = true;
    }
}
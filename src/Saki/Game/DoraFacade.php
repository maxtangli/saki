<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * @package Saki\Game
 */
class DoraFacade {
    private $deadWall;

    /**
     * @param DeadWall $deadWall
     */
    function __construct(DeadWall $deadWall) {
        $this->deadWall = $deadWall;
    }

    /**
     * @return TileList
     */
    function getIndicatorList() {
        return $this->deadWall->getIndicatorList();
    }

    /**
     * @return TileList
     */
    function getUraIndicatorList() {
        return $this->deadWall->getUraIndicatorList();
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getIndicatorList());
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileUraDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getUraIndicatorList());
    }

    /**
     * @param Tile $tile
     * @param TileList $indicatorList
     * @return int
     */
    protected function getTileDoraFanImpl(Tile $tile, TileList $indicatorList) {
        $toDoraCount = function (Tile $indicator) use ($tile) {
            return $tile == $indicator->getNextTile(1) ? 1 : 0;
        };
        return $indicatorList->getSum($toDoraCount);
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileRedDoraFan(Tile $tile) {
        return $tile->isRedDora() ? 1 : 0;
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileAllDoraFan(Tile $tile) {
        return $this->getTileDoraFan($tile)
        + $this->getTileUraDoraFan($tile)
        + $this->getTileRedDoraFan($tile);
    }

    /**
     * @param TileList $complete
     * @return int
     */
    function getHandDoraFan(TileList $complete) {
        return $this->getHandDoraFanImpl($complete, [$this, 'getTileDoraFan']);
    }

    /**
     * @param TileList $complete
     * @return int
     */
    function getHandUraDoraFan(TileList $complete) {
        return $this->getHandDoraFanImpl($complete, [$this, 'getTileUraDoraFan']);
    }

    /**
     * @param TileList $complete
     * @return int
     */
    function getHandRedDoraFan(TileList $complete) {
        return $this->getHandDoraFanImpl($complete, [$this, 'getTileRedDoraFan']);
    }

    /**
     * @param TileList $complete
     * @return int
     */
    function getHandAllDoraFan(TileList $complete) {
        return $this->getHandDoraFanImpl($complete, [$this, 'getTileAllDoraFan']);
    }

    /**
     * @param TileList $complete
     * @param callable $getDoraFanCallback
     * @return int
     */
    protected function getHandDoraFanImpl(TileList $complete, callable $getDoraFanCallback) {
        if (!$complete->getSize()->isComplete()) {
            throw new \InvalidArgumentException();
        }

        return $complete->getSum($getDoraFanCallback);
    }
}
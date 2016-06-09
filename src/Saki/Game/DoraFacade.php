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
     * @return Tile[]
     */
    function getOpenedDoraIndicators() {
        return $this->deadWall->getOpenedDoraIndicators();
    }

    /**
     * @return Tile[]
     */
    function getOpenedUraDoraIndicators() {
        return $this->deadWall->getOpenedUraDoraIndicators();
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getOpenedDoraIndicators());
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getTileUraDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getOpenedUraDoraIndicators());
    }

    /**
     * @param Tile $tile
     * @param array $openedIndicators
     * @return int
     */
    protected function getTileDoraFanImpl(Tile $tile, array $openedIndicators) {
        $count = 0;
        foreach ($openedIndicators as $doraIndicator) {
            /** @var Tile $doraIndicator */
            $doraIndicator = $doraIndicator;
            if ($tile == $doraIndicator->getNextTile(1)) {
                ++$count;
            }
        }
        return $count;
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
     * @param TileList $allTileList
     * @return int
     */
    function getHandDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileDoraFan']);
    }

    /**
     * @param TileList $allTileList
     * @return int
     */
    function getHandUraDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileUraDoraFan']);
    }

    /**
     * @param TileList $allTileList
     * @return int
     */
    function getHandRedDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileRedDoraFan']);
    }

    /**
     * @param TileList $allTileList
     * @return int
     */
    function getHandAllDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileAllDoraFan']);
    }

    /**
     * @param TileList $allTileList
     * @param callable $getDoraFanCallback
     * @return int
     */
    protected function getHandDoraFanImpl(TileList $allTileList, callable $getDoraFanCallback) {
        if (!$allTileList->getSize()->isComplete()) {
            throw new \InvalidArgumentException();
        }

        $count = 0;
        foreach ($allTileList as $tile) {
            $count += $getDoraFanCallback($tile);
        }
        return $count;
    }
}
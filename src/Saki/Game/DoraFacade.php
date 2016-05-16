<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class DoraFacade {
    private $deadWall;

    function __construct(DeadWall $deadWall) {
        $this->deadWall = $deadWall;
    }

    function getOpenedDoraIndicators() {
        return $this->deadWall->getOpenedDoraIndicators();
    }

    function getOpenedUraDoraIndicators() {
        return $this->deadWall->getOpenedUraDoraIndicators();
    }

    function getTileDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getOpenedDoraIndicators());
    }

    function getTileUraDoraFan(Tile $tile) {
        return $this->getTileDoraFanImpl($tile, $this->getOpenedUraDoraIndicators());
    }

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

    function getTileRedDoraFan(Tile $tile) {
        return $tile->isRedDora() ? 1 : 0;
    }

    function getTileAllDoraFan(Tile $tile) {
        return $this->getTileDoraFan($tile) + $this->getTileUraDoraFan($tile) + $this->getTileRedDoraFan($tile);
    }

    function getHandDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileDoraFan']);
    }

    function getHandUraDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileUraDoraFan']);
    }

    function getHandRedDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileRedDoraFan']);
    }

    function getHandAllDoraFan(TileList $allTileList) {
        return $this->getHandDoraFanImpl($allTileList, [$this, 'getTileAllDoraFan']);
    }

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
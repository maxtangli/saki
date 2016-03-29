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

    function getTileDoraFanCount(Tile $tile) {
        return $this->getTileDoraFanCountImpl($tile, $this->getOpenedDoraIndicators());
    }

    function getTileUraDoraFanCount(Tile $tile) {
        return $this->getTileDoraFanCountImpl($tile, $this->getOpenedUraDoraIndicators());
    }

    protected function getTileDoraFanCountImpl(Tile $tile, array $openedIndicators) {
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

    function getTileRedDoraFanCount(Tile $tile) {
        return $tile->isRedDora() ? 1 : 0;
    }

    function getTileAllDoraFanCount(Tile $tile) {
        return $this->getTileDoraFanCount($tile) + $this->getTileUraDoraFanCount($tile) + $this->getTileRedDoraFanCount($tile);
    }

    function getHandDoraFanCount(TileList $allTileList) {
        return $this->getHandDoraFanCountImpl($allTileList, array($this, 'getTileDoraFanCount'));
    }

    function getHandUraDoraFanCount(TileList $allTileList) {
        return $this->getHandDoraFanCountImpl($allTileList, array($this, 'getTileUraDoraFanCount'));
    }

    function getHandRedDoraFanCount(TileList $allTileList) {
        return $this->getHandDoraFanCountImpl($allTileList, array($this, 'getTileRedDoraFanCount'));
    }

    function getHandAllDoraFanCount(TileList $allTileList) {
        return $this->getHandDoraFanCountImpl($allTileList, array($this, 'getTileAllDoraFanCount'));
    }

    protected function getHandDoraFanCountImpl(TileList $allTileList, callable $getDoraFanCountCallback) {
        if (!$allTileList->getHandSize()->isCompletePrivate()) {
            throw new \InvalidArgumentException();
        }

        $count = 0;
        foreach ($allTileList as $tile) {
            $count += $getDoraFanCountCallback($tile);
        }
        return $count;
    }
}
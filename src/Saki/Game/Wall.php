<?php
namespace Saki\Game;

use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Game\Wall\LiveWall;
use Saki\Game\Wall\StackList;

/**
 * @package Saki\Game
 */
class Wall {
    private $tileSet;
    private $liveWall;
    private $deadWall;
    private $doraFacade;

    /**
     * @param TileSet $tileSet
     * @param bool $shuffle
     */
    function __construct(TileSet $tileSet, bool $shuffle = true) {
        $valid = $tileSet->count() === 136;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->tileSet = $tileSet;

        list($deadWallTileLists, $currentTileList) = $this->generateTwoParts($shuffle);
        $this->deadWall = new DeadWall($deadWallTileLists);
        $this->doraFacade = new DoraFacade($this->deadWall);
        $this->liveWall = new LiveWall();
        $this->liveWall->init(StackList::createByTileList($currentTileList));
    }

    /**
     * set $currentTileList based on $baseTileList
     * @param bool $shuffle
     */
    function reset($shuffle = true) {
        list($deadWallTileLists, $currentTileList) = $this->generateTwoParts($shuffle);
        $this->deadWall->reset($deadWallTileLists);
        $this->liveWall->init(StackList::createByTileList($currentTileList));
    }

    /**
     * @param bool $shuffle
     * @return TileList[] list($deadWallTileLists, $currentTileList)
     */
    protected function generateTwoParts(bool $shuffle = true) {
        $baseTileList = new TileList($this->getTileSet()->toArray());
        if ($shuffle) {
            $baseTileList->shuffle();
        }
        return $baseTileList->toTwoPart(14);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getDeadWall()->__toString() . ',' . $this->getLiveWall()->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        $a = $this->getDeadWall()->toJson();
        $a['remainTileCount'] = $this->getLiveWall()->getRemainTileCount();
        return $a;
    }

    /**
     * @return TileSet
     */
    function getTileSet() {
        return $this->tileSet;
    }

    /**
     * @return LiveWall
     */
    function getLiveWall() {
        return $this->liveWall;
    }

    /**
     * @return DeadWall
     */
    function getDeadWall() {
        return $this->deadWall;
    }

    /**
     * @return DoraFacade
     */
    function getDoraFacade() {
        return $this->doraFacade;
    }
}
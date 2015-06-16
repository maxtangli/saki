<?php
namespace Saki\Game;

use Saki\Tile;
use Saki\TileList;
use Saki\Util\ArrayLikeObject;

class DeadWall {
    // 0 2 |4 6 8 10 12
    // 1 3 |5 7 9 11 13
    private $tileList;
    private $doraIndicators;
    private $openedDoraIndicatorCount;
    private $uraDoraIndicators;
    private $uraDoraOpened;

    function __construct(TileList $tileList) {
        if (count($tileList) != 14) {
            throw new \InvalidArgumentException();
        }
        $this->tileList = $tileList;
        $this->doraIndicators = [$tileList[4], $tileList[6], $tileList[8], $tileList[10], $tileList[12]];
        $this->uraDoraIndicators = [$tileList[5], $tileList[7], $tileList[9], $tileList[11], $tileList[13]];
        $this->openedDoraIndicatorCount = 1;
        $this->uraDoraOpened = false;
    }

    function __toString() {
        return $this->tileList->__toString();
    }

    function openDoraIndicator($n = 1) {
        $valid = $this->openedDoraIndicatorCount + $n <= 5;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->openedDoraIndicatorCount += $n;
    }

    /**
     * @return Tile[]
     */
    function getOpenedDoraIndicators() {
        return array_slice($this->doraIndicators, $this->openedDoraIndicatorCount);
    }

    /**
     * @return Tile[]
     */
    function getOpenedUraDoraIndicators() {
        return $this->uraDoraOpened ? array_slice($this->uraDoraIndicators, $this->openedDoraIndicatorCount) : [];
    }

    /**
     * @return Tile[]
     */
    function getOpenedAllDoraIndicators() {
        return array_merge($this->getOpenedDoraIndicators(), $this->getOpenedUraDoraIndicators());
    }

    function openUraDoraIndicator() {
        $this->uraDoraOpened = true;
    }

    /**
     * @param Tile $tile
     * @return int
     */
    function getDoraYakuCount(Tile $tile) {
        $yakuCount = 0;
        foreach ($this->getOpenedAllDoraIndicators() as $doraIndicator) {
            if ($doraIndicator->toNextTile() == $tile) {
                ++$yakuCount;
            }
        }
        return $yakuCount;
    }

    function shift() {
        if ($this->tileList->count() <= 10) {
            throw new \InvalidArgumentException();
        }
        return $this->tileList->shift();
    }
}
<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * @package Saki\Game
 */
class DeadWall {
    /**
     * replacement * 4
     * 0 2 | 4 6 8 10 12 <- doraIndicator    * 5
     * 1 3 | 5 7 9 11 13 <- uraDoraIndicator * 5
     */
    /** @var  TileList */
    private $tileList;
    private $doraIndicators;
    private $openedDoraIndicatorCount;
    private $uraDoraIndicators;
    private $uraDoraOpened;

    /**
     * @param TileList $tileList
     */
    function __construct(TileList $tileList) {
        $this->reset($tileList);
    }

    /**
     * @param TileList $tileList
     * @param int $openedDoraIndicatorCount
     * @param bool $uraDoraOpened
     */
    function reset(TileList $tileList, int $openedDoraIndicatorCount = 1, bool $uraDoraOpened = false) {
        if (count($tileList) != 14) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $tileList[%s].', $tileList)
            );
        }
        $this->tileList = $tileList;
        $this->doraIndicators = [$tileList[4], $tileList[6], $tileList[8], $tileList[10], $tileList[12]];
        $this->uraDoraIndicators = [$tileList[5], $tileList[7], $tileList[9], $tileList[11], $tileList[13]];
        $this->openedDoraIndicatorCount = $openedDoraIndicatorCount;
        $this->uraDoraOpened = $uraDoraOpened;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->tileList->__toString();
    }

    /**
     * @param Tile $tile
     */
    function debugSetNextDrawReplacement(Tile $tile) {
        $this->assertAbleDrawReplacement();
        $this->tileList->replaceAt(0, $tile);
    }

    /**
     * @return Tile[]
     */
    function getOpenedDoraIndicators() {
        return array_slice($this->doraIndicators, 0, $this->openedDoraIndicatorCount);
    }

    function getOpenedDoraIndicatorList() {
        return new TileList($this->getOpenedDoraIndicators());
    }
    
    /**
     * @return Tile[]
     */
    function getOpenedUraDoraIndicators() {
        return $this->uraDoraOpened ? array_slice($this->uraDoraIndicators, 0, $this->openedDoraIndicatorCount) : [];
    }
    
    function getOpenedUraDoraIndicatorList() {
        return new TileList($this->getOpenedUraDoraIndicators());
    }

    /**
     * @param int $n
     */
    function openDoraIndicator(int $n = 1) {
        $valid = $this->openedDoraIndicatorCount + $n <= 5;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->openedDoraIndicatorCount += $n;
    }

    function openUraDoraIndicator() {
        $this->uraDoraOpened = true;
    }

    /**
     * @return Tile
     */
    function drawReplacement() {
        $this->assertAbleDrawReplacement();

        $tile = $this->tileList->getFirst();
        $this->tileList->removeFirst();

        $this->openDoraIndicator();

        return $tile;
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        return $this->tileList->count();
    }
    
    /**
     * @return bool
     */
    function isAbleDrawReplacement() {
        $remainReplacementCount = $this->tileList->count() - 10;
        return $remainReplacementCount > 0;
    }

    protected function assertAbleDrawReplacement() {
        if (!$this->isAbleDrawReplacement()) {
            throw new \InvalidArgumentException('not drawable');
        }
    }
}
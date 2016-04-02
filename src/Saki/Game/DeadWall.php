<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

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

    function __construct(TileList $tileList) {
        $this->reset($tileList);
    }

    function reset(TileList $tileList, int $openedDoraIndicatorCount = 1, bool $uraDoraOpened = false) {
        if (count($tileList) != 14) {
            throw new \InvalidArgumentException();
        }
        $this->tileList = $tileList;
        $this->doraIndicators = [$tileList[4], $tileList[6], $tileList[8], $tileList[10], $tileList[12]];
        $this->uraDoraIndicators = [$tileList[5], $tileList[7], $tileList[9], $tileList[11], $tileList[13]];
        $this->openedDoraIndicatorCount = $openedDoraIndicatorCount;
        $this->uraDoraOpened = $uraDoraOpened;
    }

    function __toString() {
        return $this->tileList->__toString();
    }

    function debugSetNextReplaceTile(Tile $tile) {
        $this->assertShiftAble();
        $this->tileList->replaceAt(0, $tile);
    }

    /**
     * @return \Saki\Tile\Tile[]
     */
    function getOpenedDoraIndicators() {
        return array_slice($this->doraIndicators, 0, $this->openedDoraIndicatorCount);
    }

    /**
     * @return \Saki\Tile\Tile[]
     */
    function getOpenedUraDoraIndicators() {
        return $this->uraDoraOpened ? array_slice($this->uraDoraIndicators, 0, $this->openedDoraIndicatorCount) : [];
    }

    function openDoraIndicator($n = 1) {
        $valid = $this->openedDoraIndicatorCount + $n <= 5;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->openedDoraIndicatorCount += $n;
    }

    function openUraDoraIndicator() {
        $this->uraDoraOpened = true;
    }

    function shift() {
        $this->assertShiftAble();
        $tile = $this->tileList->getFirst();
        $this->tileList->removeFirst();
        return $tile;
    }

    function shiftAble() {
        return $this->tileList->count() >= 10;
    }

    protected function assertShiftAble() {
        if (!$this->shiftAble()) {
            throw new \InvalidArgumentException(
                sprintf('failed to assert [%s] <= 10.', $this->tileList->count())
            );
        }
    }
}
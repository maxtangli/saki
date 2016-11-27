<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\TileList;

/**
 * @package Saki\Game
 */
class IndicatorWall {
    /**
     * 0 2 4 6 8 <- indicator    * 5
     * 1 3 5 7 9 <- uraIndicator * 5
     */
    /** @var StackList */
    private $stackList;
    private $indicatorCount;
    private $uraIndicatorOpened;

    /**
     * @param StackList $stackList
     */
    function __construct(StackList $stackList) {
        $this->reset($stackList);
    }

    /**
     * @param StackList $stackList
     * @param int $indicatorCount
     * @param bool $uraIndicatorOpened
     */
    function reset(StackList $stackList, int $indicatorCount = 1, bool $uraIndicatorOpened = false) {
        $stackList->toTileList()->assertCount(10);
        $this->stackList = $stackList;
        $this->indicatorCount = $indicatorCount;
        $this->uraIndicatorOpened = $uraIndicatorOpened;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->stackList->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        return $this->stackList->toJson(true);
    }

    /**
     * @return int
     */
    function getIndicatorCount() {
        return $this->indicatorCount;
    }

    /**
     * @return bool
     */
    function uraIndicatorOpened() {
        return $this->uraIndicatorOpened;
    }

    /**
     * @return int
     */
    function getUraIndicatorCount() {
        return $this->uraIndicatorOpened() ? $this->getIndicatorCount() : 0;
    }

    /**
     * @return TileList
     */
    function getIndicatorList() {
        return $this->stackList->toTopTileList()
            ->take(0, $this->indicatorCount);
    }

    /**
     * @return TileList
     */
    function getUraIndicatorList() {
        return $this->stackList->toBottomTileList()
            ->take(0, $this->getUraIndicatorCount());
    }

    /**
     * @param int $n
     */
    function openIndicator(int $n = 1) {
        $valid = ($this->indicatorCount + $n <= 5);
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->indicatorCount += $n;
    }

    function openUraIndicators() {
        $valid = !$this->uraIndicatorOpened;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->uraIndicatorOpened = true;
    }
}
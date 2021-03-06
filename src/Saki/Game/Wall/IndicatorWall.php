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
        $this->openIndicator($indicatorCount);
        if ($uraIndicatorOpened) {
            $this->openUraIndicators();
        }
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
        $a = $this->stackList->toJson();
        return [
            'indicatorList' => [$a[0][0], $a[1][0], $a[2][0], $a[3][0], $a[4][0]],
            'uraIndicatorList' => [$a[0][1], $a[1][1], $a[2][1], $a[3][1], $a[4][1]],
        ];
    }

    /**
     * @return TileList
     */
    function getIndicatorList() {
        $isOpened = function (Stack $stack) {
            return $stack->getTop()->isOpened();
        };
        $toTile = function (Stack $stack) {
            return $stack->getTop()->getTile();
        };
        $a = $this->stackList->getCopy()
            ->where($isOpened)
            ->toArray($toTile);
        return new TileList($a);
    }

    /**
     * @return TileList
     */
    function getUraIndicatorList() {
        $isOpened = function (Stack $stack) {
            return $stack->getBottom()->isOpened();
        };
        $toTile = function (Stack $stack) {
            return $stack->getBottom()->getTile();
        };
        $a = $this->stackList->getCopy()
            ->where($isOpened)
            ->toArray($toTile);
        return new TileList($a);
    }

    /**
     * @param int $n
     */
    function openIndicator(int $n = 1) {
        $isNotOpened = function (Stack $stack) {
            return !$stack->getTop()->isOpened();
        };
        /** @var Stack $nextNotOpenedStack */
        $openTop = function (Stack $stack) {
            $stack->getTop()->open();
        };
        $this->stackList->getCopy()
            ->where($isNotOpened)
            ->take(0, $n)
            ->walk($openTop); // validate by take()
    }

    function openUraIndicators() {
        $openedIndicatorCount = $this->getIndicatorList()->count();
        $openBottom = function (Stack $stack) {
            $stack->getBottom()->open();
        };
        $this->stackList->getCopy()
            ->take(0, $openedIndicatorCount)
            ->walk($openBottom);
    }

    /**
     * @param TileList $hand
     * @return int
     */
    function getHandDoraFan(TileList $hand) {
        return DoraType::create(DoraType::DORA)
            ->getHandFan($hand, $this->getIndicatorList());
    }

    /**
     * @param TileList $hand
     * @return int
     */
    function getHandUraDoraFan(TileList $hand) {
        return DoraType::create(DoraType::URA_DORA)
            ->getHandFan($hand, $this->getUraIndicatorList());
    }
}
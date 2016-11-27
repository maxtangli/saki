<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\TileList;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class DeadWall {
    /**
     * 0 2 4 6 8 <- indicator    * 5
     * 1 3 5 7 9 <- uraIndicator * 5
     */

    /** @var TileList */
    private $tileList;
    // indicators
    private $indicatorCandidates;
    private $uraIndicatorCandidates;
    private $indicatorCount;
    private $uraIndicatorOpened;

    /**
     * @param StackList $indicatorStackList
     */
    function __construct(StackList $indicatorStackList) {
        $this->reset($indicatorStackList->toTileList());
    }

    /**
     * @param TileList $tileList
     * @param int $indicatorCount
     * @param bool $uraIndicatorOpened
     */
    function reset(TileList $tileList, int $indicatorCount = 1, bool $uraIndicatorOpened = false) {
        if (count($tileList) != 10) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $tileList[%s].', $tileList)
            );
        }
        $this->tileList = $tileList;

        $this->indicatorCandidates = [$tileList[0], $tileList[2], $tileList[4], $tileList[6], $tileList[8]];
        $this->uraIndicatorCandidates = [$tileList[1], $tileList[3], $tileList[5], $tileList[7], $tileList[9]];
        $this->indicatorCount = $indicatorCount;
        $this->uraIndicatorOpened = $uraIndicatorOpened;
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->tileList->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        $format = function (TileList $indicatorList) {
            return $indicatorList
                ->select(Utils::getToStringCallback())
                ->fillToCount('O', 5)
                ->toArray();
        };
        $indicators = $format($this->getIndicatorList());
        $uraIndicators = $format($this->getUraIndicatorList());

        $stacks = [];
        foreach (range(0, 4) as $i) {
            $stacks[] = [$indicators[$i], $uraIndicators[$i]];
        }

        return [
            'stacks' => $stacks,
        ];
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
        return (new TileList($this->indicatorCandidates))
            ->take(0, $this->indicatorCount);
    }

    /**
     * @return TileList
     */
    function getUraIndicatorList() {
        return (new TileList($this->uraIndicatorCandidates))
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
        $this->uraIndicatorOpened = true;
    }
}
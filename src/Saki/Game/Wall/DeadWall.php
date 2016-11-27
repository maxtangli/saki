<?php
namespace Saki\Game\Wall;

use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class DeadWall {
    /**
     * replacement * 4
     * 0 2 | 4 6 8 10 12 <- indicator    * 5
     * 1 3 | 5 7 9 11 13 <- uraIndicator * 5
     */

    /** @var TileList */
    private $tileList;
    // replacement
    private $replacementWall;
    // indicators
    private $indicatorCandidates;
    private $uraIndicatorCandidates;
    private $indicatorCount;
    private $uraIndicatorOpened;

    /**
     * @param StackList $stackList
     */
    function __construct(StackList $stackList) {
        $this->replacementWall = new ReplacementWall();
        $this->reset($stackList->toTileList());
    }

    /**
     * @param TileList $tileList
     * @param int $indicatorCount
     * @param bool $uraIndicatorOpened
     */
    function reset(TileList $tileList, int $indicatorCount = 1, bool $uraIndicatorOpened = false) {
        if (count($tileList) != 14) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $tileList[%s].', $tileList)
            );
        }
        $this->tileList = $tileList;

        $replacementList = $tileList->getCopy()->take(0, 4);
        $this->replacementWall->init(StackList::fromTileList($replacementList));

        $this->indicatorCandidates = [$tileList[4], $tileList[6], $tileList[8], $tileList[10], $tileList[12]];
        $this->uraIndicatorCandidates = [$tileList[5], $tileList[7], $tileList[9], $tileList[11], $tileList[13]];
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
        $a = array_fill(0, $this->getRemainReplacementCount(), 'O');
        $replacements = (new ArrayList($a))
            ->fillToCount('X', 4)
            ->toArray();

        $format = function (TileList $indicatorList) {
            return $indicatorList
                ->select(Utils::getToStringCallback())
                ->fillToCount('O', 5)
                ->toArray();
        };
        $indicators = $format($this->getIndicatorList());
        $uraIndicators = $format($this->getUraIndicatorList());

        $stacks = array_chunk($replacements, 2);
        foreach (range(0, 4) as $i) {
            $stacks[] = [$indicators[$i], $uraIndicators[$i]];
        }

        return [
            'replacements' => $replacements,
            'indicators' => $indicators,
            'uraIndicators' => $uraIndicators,
            'stacks' => $stacks,
        ];
    }

    /**
     * @return ReplacementWall
     */
    function getReplacementWall() {
        return $this->replacementWall;
    }

    /**
     * @return int
     */
    private function getRemainReplacementCount() {
        return $this->replacementWall->getRemainTileCount();
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        return $this->getRemainReplacementCount() + 10;
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
        $valid = $this->indicatorCount + $n <= 5;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->indicatorCount += $n;
    }

    function openUraIndicators() {
        $this->uraIndicatorOpened = true;
    }
}
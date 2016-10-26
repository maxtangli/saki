<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
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
    /** @var  TileList */
    private $tileList;
    /** @var  TileList */
    private $replacementList;
    private $indicatorCandidates;
    private $uraIndicatorCandidates;
    private $indicatorCount;
    private $uraIndicatorOpened;

    /**
     * @param TileList $tileList
     */
    function __construct(TileList $tileList) {
        $this->reset($tileList);
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
        $this->replacementList = $tileList->getCopy()->take(0, 4);
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
     * @param Tile $tile
     */
    function debugSetNextReplacement(Tile $tile) {
        $this->assertAbleDrawReplacement();
        $this->replacementList->replaceAt(0, $tile);
    }

    /**
     * @return TileList
     */
    function getReplacementList() {
        return $this->replacementList->getCopy();
    }

    /**
     * @return int
     */
    function getRemainReplacementCount() {
        return $this->replacementList->count();
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
     * @return Tile
     */
    function drawReplacement() {
        $this->assertAbleDrawReplacement();

        $tile = $this->replacementList->getFirst();
        $this->replacementList->removeFirst();

        $this->openIndicator();

        return $tile;
    }

    /**
     * @return bool
     */
    function isAbleDrawReplacement() {
        return $this->getRemainReplacementCount() > 0;
    }

    protected function assertAbleDrawReplacement() {
        if (!$this->isAbleDrawReplacement()) {
            throw new \InvalidArgumentException('not drawable');
        }
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
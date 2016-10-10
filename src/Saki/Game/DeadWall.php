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
     * 0 2 | 4 6 8 10 12 <- indicator    * 5
     * 1 3 | 5 7 9 11 13 <- uraIndicator * 5
     */
    /** @var  TileList */
    private $tileList;
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
    function toJsonArray() {
        return []; // todo
    }

    /**
     * @param Tile $tile
     */
    function debugSetNextReplacement(Tile $tile) {
        $this->assertAbleDrawReplacement();
        $this->tileList->replaceAt(0, $tile);
    }

    /**
     * @return int
     */
    function getRemainTileCount() {
        return $this->tileList->count();
    }

    /**
     * @return int
     */
    function getRemainReplacementCount() {
        return $this->getRemainTileCount() - 10;
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

        $tile = $this->tileList->getFirst();
        $this->tileList->removeFirst();

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
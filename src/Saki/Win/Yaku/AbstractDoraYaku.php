<?php
namespace Saki\Win\Yaku;

use Saki\Game\Tile\TileList;
use Saki\Game\Wall\IndicatorWall;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
abstract class AbstractDoraYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 1;
    }

    protected function getExistCountImpl(WinSubTarget $subTarget) {
        $indicatorWall = $subTarget->getRound()->getWall()->getIndicatorWall();
        return $this->getDoraFanImpl($subTarget->getHand()->getComplete(), $indicatorWall);
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $this->getExistCountImpl($subTarget) > 0;
    }

    /**
     * @param TileList $complete
     * @param IndicatorWall $indicatorWall
     * @return int
     */
    abstract function getDoraFanImpl(TileList $complete, IndicatorWall $indicatorWall);
}
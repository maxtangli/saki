<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 混一色（ホン​イーソー）
 * @package Saki\Win\Yaku\Fan3
 */
class HalfFlushYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 3;
    }

    protected function getNotConcealedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllTileSortedList(true)->isFlush(false);
    }
}



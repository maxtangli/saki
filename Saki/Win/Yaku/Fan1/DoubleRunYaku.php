<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 一盃口
 * @package Saki\Win\Yaku\Fan1
 */
class DoubleRunYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 1;
    }

    protected function getExposedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isDoubleRun(false);
    }
}
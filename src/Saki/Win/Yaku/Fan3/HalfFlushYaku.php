<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 混一色
 * @package Saki\Win\Yaku\Fan3
 */
class HalfFlushYaku extends Yaku {
    function getConcealedFan() {
        return 3;
    }

    function getNotConcealedFan() {
        return 2;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getComplete()->isFlush(false);
    }
}



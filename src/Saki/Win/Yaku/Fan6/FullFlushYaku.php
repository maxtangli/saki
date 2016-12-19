<?php
namespace Saki\Win\Yaku\Fan6;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan3\HalfFlushYaku;
use Saki\Win\Yaku\Yaku;

/**
 * 清一色
 * @package Saki\Win\Yaku\Fan6
 */
class FullFlushYaku extends Yaku {
    function getConcealedFan() {
        return 6;
    }

    function getNotConcealedFan() {
        return 5;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getHand()->getComplete()->isFlush(true);
    }

    function getExcludedYakus() {
        return [HalfFlushYaku::create()];
    }
}
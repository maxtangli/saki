<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 大四喜
 * @package Saki\Win\Yaku\Yakuman
 */
class BigFourWindsYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 13;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isFourWinds(true);
    }

    function getExcludedYakus() {
        return [LittleFourWindsYaku::create()];
    }
}
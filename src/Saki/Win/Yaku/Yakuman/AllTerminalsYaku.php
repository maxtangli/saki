<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 清老頭
 * @package Saki\Win\Yaku\Yakuman
 */
class AllTerminalsYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 13;
    }

    function getRequiredSeries() {
        return [Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isAllTerminals();
    }
}


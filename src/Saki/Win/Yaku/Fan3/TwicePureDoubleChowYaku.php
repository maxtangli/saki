<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\PureDoubleChowYaku;
use Saki\Win\Yaku\Yaku;

/**
 * 二盃口
 * @package Saki\Win\Yaku\Fan3
 */
class TwicePureDoubleChowYaku extends Yaku {
    function getConcealedFan() {
        return 3;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isDoubleChow(true);
    }

    function getExcludedYakus() {
        return [PureDoubleChowYaku::create()];
    }
}
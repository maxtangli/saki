<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Yaku;

class TwoDoubleRunYaku extends Yaku {
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
        return $subTarget->getAllMeldList()->isDoubleRun(true);
    }

    function getExcludedYakus() {
        return [DoubleRunYaku::create()];
    }
}
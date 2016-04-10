<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Yaku;

class TwoDoubleRunYaku extends Yaku {
    function getConcealedFanCount() {
        return 3;
    }

    function getNotConcealedFanCount() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isDoubleRun(true);
    }

    function getExcludedYakus() {
        return [DoubleRunYaku::create()];
    }
}
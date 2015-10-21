<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\DoubleRunYaku;
use Saki\Win\Yaku\Yaku;

class TwoDoubleRunYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 3;
    }

    protected function getExposedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isDoubleRun(true);
    }

    function getExcludedYakus() {
        return [DoubleRunYaku::getInstance()];
    }
}
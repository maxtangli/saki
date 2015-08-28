<?php
namespace Saki\Win\Yaku;

use Saki\Win\WinSubTarget;

class ReachYaku extends Yaku {
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
        return $subTarget->isReach();
    }
}
<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Yaku;

class DoubleReachYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getNotConcealedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->isDoubleReach();
    }

    function getExcludedYakus() {
        return [ReachYaku::getInstance()];
    }
}
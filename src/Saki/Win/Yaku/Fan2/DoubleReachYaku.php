<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Yaku;

class DoubleReachYaku extends Yaku {
    function getConcealedFanCount() {
        return 2;
    }

    function getNotConcealedFanCount() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getReachStatus()->isDoubleReach();
    }

    function getExcludedYakus() {
        return [ReachYaku::create()];
    }
}
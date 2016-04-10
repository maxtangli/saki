<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan1\ReachYaku;
use Saki\Win\Yaku\Yaku;

class DoubleReachYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getReachStatus()->isDoubleReach();
    }

    function getExcludedYakus() {
        return [ReachYaku::create()];
    }
}
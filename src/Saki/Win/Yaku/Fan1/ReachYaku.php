<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ReachYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getNotConcealedFanCount() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getReachStatus()->isReach();
    }
}


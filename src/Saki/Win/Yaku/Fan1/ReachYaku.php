<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ReachYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 1;
    }

    protected function getNotConcealedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getReachStatus()->isReach();
    }
}


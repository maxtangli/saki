<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class FullStraightYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getNotConcealedFanCount() {
        return 1;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isFullStraight();
    }
}


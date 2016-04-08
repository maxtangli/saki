<?php
namespace Saki\Win\Yaku\Fan6;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan3\HalfFlushYaku;
use Saki\Win\Yaku\Yaku;

class FullFlushYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 6;
    }

    protected function getNotConcealedFanCount() {
        return 5;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateComplete()->isFlush(true);
    }

    function getExcludedYakus() {
        return [HalfFlushYaku::create()];
    }
}
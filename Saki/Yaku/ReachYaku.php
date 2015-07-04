<?php
namespace Saki\Yaku;

use Saki\Win\WinAnalyzerSubTarget;

class ReachYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getExposedFanCount() {
        return 0;
    }

    protected function existInImpl(WinAnalyzerSubTarget $subTarget) {
        return $subTarget->isReach();
    }
}
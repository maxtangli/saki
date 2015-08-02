<?php
namespace Saki\Win;

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
<?php
namespace Saki\Yaku;

class ReachYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getExposedFanCount() {
        return 0;
    }

    protected function existInImpl(YakuAnalyzerSubTarget $subTarget) {
        return $subTarget->isReach();
    }
}
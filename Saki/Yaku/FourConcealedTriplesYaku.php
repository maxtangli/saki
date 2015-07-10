<?php
namespace Saki\Yaku;

use Saki\Win\WinAnalyzerSubTarget;
use Saki\Meld\Meld;
class FourConcealedTriplesYaku extends Yaku {
    function getConcealedFanCount() {
        return 13;
    }

    function getExposedFanCount() {
        return 13;
    }

    function existInImpl(WinAnalyzerSubTarget $subTarget) {
        return $subTarget->is4TripleOrQuadAnd1Pair(true);
    }
}


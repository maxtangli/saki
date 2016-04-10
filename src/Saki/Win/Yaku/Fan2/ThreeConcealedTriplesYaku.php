<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ThreeConcealedTriplesYaku extends Yaku {
    function getConcealedFanCount() {
        return 2;
    }

    function getNotConcealedFanCount() {
        return 2;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThreeConcealedTripleOrQuads();
    }
}
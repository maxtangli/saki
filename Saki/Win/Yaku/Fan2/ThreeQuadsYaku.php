<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class ThreeQuadsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getExposedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThreeOrFourQuads(false);
    }
}
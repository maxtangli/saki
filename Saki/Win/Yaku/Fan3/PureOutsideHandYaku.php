<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class PureOutsideHandYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 3;
    }

    protected function getExposedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isOutsideHand(true);
    }
}
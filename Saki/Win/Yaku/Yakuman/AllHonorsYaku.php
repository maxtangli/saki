<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class AllHonorsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 13;
    }

    protected function getExposedFanCount() {
        return 13;
    }

    protected function getRequiredTileSeries() {
        return [TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isAllHonors();
    }
}
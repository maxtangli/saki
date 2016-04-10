<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class AllGreenYaku extends Yaku {
    function getConcealedFanCount() {
        return 13;
    }

    function getNotConcealedFanCount() {
        return 13;
    }

    function getRequiredTileSeries() {
        return [TileSeries::create(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateComplete()->isAllGreen();
    }
}
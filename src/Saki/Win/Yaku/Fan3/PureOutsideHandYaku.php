<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan2\MixedOutsideHandYaku;
use Saki\Win\Yaku\Yaku;

class PureOutsideHandYaku extends Yaku {
    function getConcealedFan() {
        return 3;
    }

    function getNotConcealedFan() {
        return 2;
    }

    function getRequiredTileSeries() {
        return [TileSeries::create(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isOutsideHand(true);
    }

    function getExcludedYakus() {
        return [MixedOutsideHandYaku::create()];
    }
}
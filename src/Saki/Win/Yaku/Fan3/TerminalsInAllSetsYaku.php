<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan2\OutsideHandYaku;
use Saki\Win\Yaku\Yaku;

class TerminalsInAllSetsYaku extends Yaku {
    function getConcealedFan() {
        return 3;
    }

    function getNotConcealedFan() {
        return 2;
    }

    function getRequiredSeries() {
        return [Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isOutsideHand(true);
    }

    function getExcludedYakus() {
        return [OutsideHandYaku::create()];
    }
}
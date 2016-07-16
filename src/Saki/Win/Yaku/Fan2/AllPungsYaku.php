<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\Series\Series;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 対々和
 * @package Saki\Win\Yaku\Fan2
 */
class AllPungsYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 2;
    }

    function getRequiredSeries() {
        return [
            Series::create(Series::FOUR_WIN_SET_AND_ONE_PAIR)
        ];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isFourPungsOrKongsAndAPair();
    }
}

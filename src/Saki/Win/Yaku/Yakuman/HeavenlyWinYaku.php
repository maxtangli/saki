<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class HeavenlyWinYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 13;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->isHeavenlyWin();
    }
}


<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 三槓子
 * @package Saki\Win\Yaku\Fan2
 */
class ThreeKongsYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 2;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isThreeOrFourKongs(false);
    }
}
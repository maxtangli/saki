<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 一気通貫
 * @package Saki\Win\Yaku\Fan2
 */
class PureStraightYaku extends Yaku {
    function getConcealedFan() {
        return 2;
    }

    function getNotConcealedFan() {
        return 1;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isPureStraight();
    }
}


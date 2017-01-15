<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 流局満貫
 * @package Saki\Win\Yaku\Special
 */
class NagashiManganYaku extends Yaku {
    function getConcealedFan() {
        return 4;
    }

    function getNotConcealedFan() {
        return 4;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        $isExhaustiveDraw = null; // todo
        $isDiscardAllTermOrHonor = null; // todo
        $isDiscardNotDeclaredByOther = null; // todo
        return $isExhaustiveDraw
            && $isDiscardAllTermOrHonor
            && $isDiscardNotDeclaredByOther;
    }
}
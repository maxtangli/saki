<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 河底撈魚
 * @package Saki\Win\Yaku\Fan1
 */
class BottomOfTheSeaFishYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getWall()->getDrawWall()->isEmpty()
        && $subTarget->getPhase()->isPublic();
    }
}


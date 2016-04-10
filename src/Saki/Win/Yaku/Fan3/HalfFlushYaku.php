<?php
namespace Saki\Win\Yaku\Fan3;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 混一色（ホン​イーソー）
 * @package Saki\Win\Yaku\Fan3
 */
class HalfFlushYaku extends Yaku {
    function getConcealedFanCount() {
        return 3;
    }

    function getNotConcealedFanCount() {
        return 2;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateComplete()->isFlush(false);
    }
}



<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 混全帯么九（ホンチャンタイヤオチュウ）
 * @package Saki\Win\Yaku\Fan2
 */
class MixedOutsideHandYaku extends Yaku {
    function getConcealedFanCount() {
        return 2;
    }

    function getNotConcealedFanCount() {
        return 1;
    }

    function getRequiredTileSeries() {
        return [TileSeries::create(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isOutsideHand(false);
    }
}


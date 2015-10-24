<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 混一色
 * @package Saki\Win\Yaku\Fan2
 */
class AllTerminalsAndHonorsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getExposedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isAllTerminalsAndHonors();
    }
}
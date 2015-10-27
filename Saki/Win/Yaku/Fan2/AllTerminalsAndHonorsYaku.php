<?php
namespace Saki\Win\Yaku\Fan2;

use Saki\Win\TileSeries;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Fan3\PureOutsideHandYaku;
use Saki\Win\Yaku\Yaku;

/**
 * 混老頭（ホンロウトウ）
 * @package Saki\Win\Yaku\Fan2
 */
class AllTerminalsAndHonorsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 2;
    }

    protected function getNotConcealedFanCount() {
        return 2;
    }

    protected function getRequiredTileSeries() {
        return [TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR)];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isAllTerminalsAndHonors();
    }
}
<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;

class PureNineGatesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 26;
    }

    protected function getNotConcealedFanCount() {
        return 0;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateComplete()->isNineGates(true, $subTarget->getTileOfTargetTile());
    }

    function getExcludedYakus() {
        return [NineGatesYaku::create()];
    }
}
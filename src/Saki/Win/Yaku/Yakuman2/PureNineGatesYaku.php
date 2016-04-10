<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;

class PureNineGatesYaku extends Yaku {
    function getConcealedFanCount() {
        return 26;
    }

    function getNotConcealedFanCount() {
        return 0;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateComplete()->isNineGates(true, $subTarget->getTileOfTargetTile());
    }

    function getExcludedYakus() {
        return [NineGatesYaku::create()];
    }
}
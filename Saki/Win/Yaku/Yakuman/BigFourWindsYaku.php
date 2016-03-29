<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class BigFourWindsYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 13;
    }

    protected function getNotConcealedFanCount() {
        return 13;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isFourWinds(true);
    }

    function getExcludedYakus() {
        return [SmallFourWindsYaku::getInstance()];
    }
}

class NineGatesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 13;
    }

    protected function getNotConcealedFanCount() {
        return 13;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateFull()->isNineGates(false);
    }
}

class PureNineGatesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 26;
    }

    protected function getNotConcealedFanCount() {
        return 26;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getPrivateFull()->isNineGates(true, $subTarget->getTileOfTargetTile());
    }

    function getExcludedYakus() {
        return [NineGatesYaku::getInstance()];
    }
}
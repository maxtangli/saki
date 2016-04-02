<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

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
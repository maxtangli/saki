<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 地和
 * @package Saki\Win\Yaku\Yakuman
 */
class BlessingOfEarthYaku extends Yaku {
    function getConcealedFan() {
        return 13;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        $actor = $subTarget->getActor();
        return $subTarget->getRound()->getTurnHolder()->isFirstTurnAndNoClaim($actor)
        && $subTarget->getPhase()->isPrivate()
        && $actor->isLeisureFamily();
    }
}
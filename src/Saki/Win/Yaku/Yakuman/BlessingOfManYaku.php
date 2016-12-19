<?php
namespace Saki\Win\Yaku\Yakuman;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 人和
 * @package Saki\Win\Yaku\Yakuman
 */
class BlessingOfManYaku extends Yaku {
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
        return $subTarget->getRound()->getTurnHolder()->isFirstTurnAndNoClaim($subTarget->getActor())
        && $subTarget->getRound()->getPhase()->isPublic()
        && $subTarget->getActorArea()->getDiscard()->isEmpty();
    }
}
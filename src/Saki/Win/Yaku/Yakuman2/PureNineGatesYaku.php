<?php
namespace Saki\Win\Yaku\Yakuman2;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;
use Saki\Win\Yaku\Yakuman\NineGatesYaku;

/**
 * 純正九蓮宝燈
 * @package Saki\Win\Yaku\Yakuman2
 */
class PureNineGatesYaku extends Yaku {
    function getConcealedFan() {
        return 26;
    }

    function getNotConcealedFan() {
        return 0;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        $targetTile = $subTarget->getHand()->getTarget()->getTile();
        return $subTarget->getHand()->getComplete()->isNineGates(true, $targetTile);
    }

    function getExcludedYakus() {
        return [NineGatesYaku::create()];
    }
}
<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\AbstractValueTilesYaku;

/**
 * 役牌　自風
 * @package Saki\Win\Yaku\Fan1
 */
class SelfWindValueTilesYaku extends AbstractValueTilesYaku {
    function getValueTile(WinSubTarget $subTarget) {
        return $subTarget->getSelfWind();
    }
}
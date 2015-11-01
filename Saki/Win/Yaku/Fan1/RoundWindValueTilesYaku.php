<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\AbstractValueTilesYaku;

/**
 * 役牌　場風
 * @package Saki\Win\Yaku\Fan1
 */
class RoundWindValueTilesYaku extends AbstractValueTilesYaku
{
    function getValueTile(WinSubTarget $subTarget)
    {
        return $subTarget->getRoundWind();
    }
}
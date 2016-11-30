<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Game\Tile\TileList;
use Saki\Game\Wall\DoraType;
use Saki\Game\Wall\IndicatorWall;
use Saki\Win\Yaku\AbstractDoraYaku;

/**
 * 赤ドラ
 * @package Saki\Win\Yaku\Fan1
 */
class RedDoraYaku extends AbstractDoraYaku {
    function getDoraFanImpl(TileList $complete, IndicatorWall $indicatorWall) {
        return DoraType::create(DoraType::RED_DORA)
            ->getHandFan($complete);
    }
}
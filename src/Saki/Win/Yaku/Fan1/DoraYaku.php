<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Game\Tile\TileList;
use Saki\Game\Wall\DoraType;
use Saki\Game\Wall\IndicatorWall;
use Saki\Win\Yaku\AbstractDoraYaku;

/**
 * ドラ
 * @package Saki\Win\Yaku\Fan1
 */
class DoraYaku extends AbstractDoraYaku {
    function getDoraFanImpl(TileList $complete, IndicatorWall $indicatorWall) {
        return $indicatorWall->getHandDoraFan($complete);
    }
}
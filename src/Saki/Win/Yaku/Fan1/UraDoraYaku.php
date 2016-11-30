<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Game\Tile\TileList;
use Saki\Game\Wall\IndicatorWall;
use Saki\Win\Yaku\AbstractDoraYaku;

/**
 * 裏ドラ
 * @package Saki\Win\Yaku\Fan1
 */
class UraDoraYaku extends AbstractDoraYaku {
    function getDoraFanImpl(TileList $complete, IndicatorWall $indicatorWall) {
        return $indicatorWall->getHandUraDoraFan($complete);
    }
}
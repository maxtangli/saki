<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Game\DoraFacade;
use Saki\Game\Tile\TileList;
use Saki\Win\Yaku\AbstractDoraYaku;

/**
 * 赤ドラ
 * @package Saki\Win\Yaku\Fan1
 */
class RedDoraYaku extends AbstractDoraYaku {
    function getDoraFanImpl(DoraFacade $doraFacade, TileList $complete) {
        return $doraFacade->getHandRedDoraFan($complete);
    }
}
<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Game\DoraFacade;
use Saki\Tile\TileList;
use Saki\Win\Yaku\AbstractDoraYaku;

/**
 * ドラ
 * @package Saki\Win\Yaku\Fan1
 */
class DoraYaku extends AbstractDoraYaku {
    function getDoraFanImpl(DoraFacade $doraFacade, TileList $complete) {
        return $doraFacade->getHandDoraFan($complete);
    }
}
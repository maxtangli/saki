<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\AbstractValueTilesYaku;

/**
 * 役牌　発
 * @package Saki\Win\Yaku\Fan1
 */
class DragonPungGreenYaku extends AbstractValueTilesYaku {
    function getValueTile(WinSubTarget $subTarget) {
        return Tile::fromString('F');
    }
}
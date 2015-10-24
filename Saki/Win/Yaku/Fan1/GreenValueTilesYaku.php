<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\AbstractValueTilesYaku;

/**
 * 役牌　発
 * @package Saki\Win\Yaku\Fan1
 */
class GreenValueTilesYaku extends AbstractValueTilesYaku {
    function isValueTile(Tile $tile, WinSubTarget $subTarget) {
        return $tile == Tile::fromString('F');
    }
}
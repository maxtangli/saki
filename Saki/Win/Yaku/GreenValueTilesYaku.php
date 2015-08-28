<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

class GreenValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinSubTarget $subTarget) {
        return $tile == Tile::fromString('P');
    }
}
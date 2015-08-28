<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

class RedValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinSubTarget $subTarget) {
        return $tile == Tile::fromString('C');
    }
}
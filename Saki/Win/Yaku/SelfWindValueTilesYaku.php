<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

class SelfWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinSubTarget $subTarget) {
        return $tile == $subTarget->getSelfWind();
    }
}
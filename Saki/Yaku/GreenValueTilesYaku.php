<?php
namespace Saki\Yaku;

use Saki\Tile;

class GreenValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('P');
    }
}
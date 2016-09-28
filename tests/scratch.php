<?php

use Saki\Tile\Tile;
use Saki\Tile\TileSet;

require_once __DIR__ . '/../bootstrap.php';

//var_dump((new Round())->toJson());

$toCss = function (Tile $tile) {
    $s = '.tile-#TILE# {background-image: url("images/#TILE#.png");}';
    return str_replace('#TILE#', $tile->__toString(), $s);
};
echo TileSet::createStandard()
    ->toUniqueTileList()
    ->select($toCss)
    ->toFormatString("\n");
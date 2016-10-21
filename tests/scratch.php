<?php

require_once __DIR__ . '/../bootstrap.php';

//var_dump((new Round())->toJson());

//$toCss = function (Tile $tile) {
//    $s = '.tile-#TILE# {background-image: url("images/#TILE#.png");}';
//    return str_replace('#TILE#', $tile->__toString(), $s);
//};
//echo TileSet::createStandard()
//    ->toUniqueTileList()
//    ->select($toCss)
//    ->toFormatString("\n");

//$l = TileList::fromString('123456789m12344p'); // 0.018ms => 0.013ms, 0.70.
////$l = TileSet::createStandard()->toTileList(); // 1.00ms => 0.11ms, 0.11.
//$origin = function () use ($l) {
//    $l->orderByTileID();
//};
//$optimize = function () use ($l) {
//    $l->orderByTileIDFast();
//};
//echo MsTimer::create()->vs($origin, $optimize);
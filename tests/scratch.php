<?php
use Saki\Tile\TileSet;
use Saki\Util\MsTimer;

require_once __DIR__ . '/../bootstrap.php';

$ratioMap = [
    // $isWinBySelf =>
    //  $winnerIsDealer => $loserIsDealer => $ratio
    true => [
        true => [true => 'error', false => 2,],
        false => [true => 2, false => 1,],
    ],
    false => [
        true => [true => 'error', false => 'all',],
        false => [true => 'error', false => 'all',],
    ],
];
var_export($ratioMap);

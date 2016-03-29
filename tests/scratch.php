<?php
use Saki\Tile\Tile;
use Saki\Tile\TileSet;
use Saki\Tile\TileType;
use Saki\Util\MsTimer;

require_once __DIR__ . '/../bootstrap.php';

echo MsTimer::getInstance()->measure(function () {
    $s = TileSet::getStandardTileSet();
});
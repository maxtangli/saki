<?php
use Saki\Tile\TileSet;
use Saki\Util\MsTimer;

require_once __DIR__ . '/../bootstrap.php';

echo MsTimer::create()->measure(function () {
    $s = TileSet::getStandardTileSet();
});
<?php

use Saki\Tile\Tile;

class TileSetTest extends PHPUnit_Framework_TestCase {
    function testUnique() {
        $tileSet = new \Saki\Tile\TileSet(\Saki\Tile\TileSet::getStandardTileSet());
        $this->assertCount(9+9+9+4+3, $tileSet->getUniqueTiles());
    }
}
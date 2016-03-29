<?php

use Saki\Tile\TileSet;

class TileSetTest extends PHPUnit_Framework_TestCase {
    function testUnique() {
        $tileSet = TileSet::getStandardTileSet();
        $n = $tileSet->count();
        $this->assertCount(9 + 9 + 9 + 4 + 3, $tileSet->getUniqueTiles());
        $this->assertEquals($n, $tileSet->count());
    }
}
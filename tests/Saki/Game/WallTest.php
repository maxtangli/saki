<?php

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Game\Wall;

class WallTest extends \SakiTestCase {
    function testAll() {
        $tileCount = 136;

        // new, no shuffle
        $wall = new Wall(TileSet::createStandard());
        $this->assertCount($tileCount, $wall->getTileSet());

        // reset and shuffle
        $wall->reset();

        // pop
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEESSSSWWWWNNNNCCCCPPPPFFFF';
        $tileList = TileList::fromString($s);
        $tileSet = new TileSet($tileList->toArray());
        $wall = new Wall($tileSet);
        $wall->reset(false);
        $tile = $wall->getLiveWall()->draw();
        $this->assertEquals(Tile::fromString('F'), $tile);

        // shift
        $tile = $wall->getDeadWall()->drawReplacement();
        $this->assertEquals(Tile::fromString('1m'), $tile);
    }

    function LiveWallTest() {

    }
}
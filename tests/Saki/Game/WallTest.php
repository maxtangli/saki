<?php

use Saki\Game\Wall;
use Saki\Tile\TileSet;

class WallTest extends \SakiTestCase {
    function testOverall() {
        $standardCnt = 136;

        // new, no shuffle
        $w = new Wall(TileSet::createStandard());
        $this->assertCount($standardCnt, $w->getTileSet());

        // reset and shuffle
        $w->reset(true);

        // pop
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEESSSSWWWWNNNNCCCCPPPPFFFF';
        $tileList = \Saki\Tile\TileList::fromString($s);
        $tileSet = new TileSet($tileList->toArray());
        $w = new Wall($tileSet);
        $t = $w->draw();
        $this->assertEquals(\Saki\Tile\Tile::fromString('F'), $t);

        // shift
        $t = $w->getDeadWall()->drawReplacement();
        $this->assertEquals(\Saki\Tile\Tile::fromString('1m'), $t);
    }
}
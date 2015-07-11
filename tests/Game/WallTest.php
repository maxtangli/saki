<?php

use Saki\Game\Wall;

class WallTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $standardCnt = 136;

        // new, no shuffle
        $w = new Wall(Wall::getStandardTileList());
        $this->assertCount($standardCnt, $w->getBaseTileReadonlyList());

        // init and shuffle
        $w->reset(true);

        // pop
        $s = '111122223333444455556666777788889999m' .
            '111122223333444455556666777788889999p' .
            '111122223333444455556666777788889999s' .
            'EEEESSSSWWWWNNNNCCCCPPPPFFFF';
        $tileList = \Saki\Tile\TileList::fromString($s);
        $w = new Wall($tileList);
        $t = $w->pop();
        $this->assertEquals(\Saki\Tile\Tile::fromString('F'), $t);

        // pop many
        $ts = $w->pop(4);
        $this->assertCount(4, $ts);
        $this->assertEquals(\Saki\Tile\Tile::fromString('F'), $ts[0]);
        $this->assertEquals(\Saki\Tile\Tile::fromString('F'), $ts[1]);
        $this->assertEquals(\Saki\Tile\Tile::fromString('F'), $ts[2]);
        $this->assertEquals(\Saki\Tile\Tile::fromString('P'), $ts[3]);

        // shift
        $t = $w->shift();
        $this->assertEquals(\Saki\Tile\Tile::fromString('1m'), $t);
    }
}
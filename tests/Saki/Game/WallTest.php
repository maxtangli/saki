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

    function StackTest() {
        $stack = new Wall\Stack();
        $t0 = Tile::fromString('1s');
        $t1 = Tile::fromString('2s');

        $stack->setTileChunk([$t0, $t1]);
        $this->assertEquals($t0, $stack->popTile());
        $this->assertEquals($t1, $stack->popTile());

        $stack->setTileChunk([$t0, $t1]);
        $mockNext = Tile::fromString('E');
        $stack->setNextPopTile($mockNext);
        $this->assertEquals($mockNext, $stack->popTile());
    }

    function LiveWallTest() {
        $liveWall = new Wall\LiveWall();
        $tileList = TileList::fromString('1234s');
        $liveWall->init(Wall\StackList::createByTileList($tileList));

        static::assertLiveWallDraw($tileList[3], 2, 3);
        static::assertLiveWallDraw($tileList[2], 1, 2);
        static::assertLiveWallDraw($tileList[1], 1, 1);
        static::assertLiveWallDraw($tileList[0], 0, 0);

        $liveWall->init(Wall\StackList::createByTileList($tileList));
        $mockNext = Tile::fromString('E');
        $liveWall->debugSetNextDrawTile($mockNext);
        static::assertLiveWallDraw($mockNext, 2, 3, $liveWall);
        $liveWall->debugSetRemainTileCount(1);
        static::assertLiveWallDraw($tileList[0], 0, 0);
        // test deal todo
    }

    static function assertLiveWallDraw(Tile $tile, int $stackCount, int $tileCount, Wall\LiveWall $liveWall) {
        static::assertEquals($tile, $liveWall->draw());
        static::assertEquals($stackCount, $liveWall->getRemainStackCount());
        static::assertEquals($tileCount, $liveWall->getRemainTileCount());
    }
}
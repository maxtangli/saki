<?php

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Game\Wall;
use Saki\Game\Wall\LiveWall;
use Saki\Game\Wall\Stack;
use Saki\Game\Wall\StackList;

class WallTest extends \SakiTestCase {
    function testStack() {
        $stack = new Stack();
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

    function testStackList() {
        $tileList = TileList::fromString('0123s');
        $stackList = StackList::createByTileList($tileList);
        $this->assertEquals($tileList, $stackList->toTileList());
    }

    function testStackListBreak() {
        // E       S        W        N
        // 0       1        2        3
        // 0...16, 17...33, 34...50, 51...67
        // e.x. dice 5, last 16, aliveFirst 11, dead 12...18, live 11...0,67...19
        $stackList = StackList::createByTileList(TileSet::createStandard());
        $diceResult = 5;
        $twoBreak = $stackList->toTwoBreak($diceResult);

        $liveStackList = $twoBreak[0];
        $this->assertSame($stackList[11], $liveStackList->getLast());
        $this->assertSame($stackList[19], $liveStackList->getFirst());

        $deadStackList = $twoBreak[1];
        $this->assertSame($stackList[12], $deadStackList->getFirst());
        $this->assertSame($stackList[18], $deadStackList->getLast());
    }

    function testLiveWall() {
        $liveWall = new LiveWall();
        $tileList = TileList::fromString('0123s');
        $liveWall->init(StackList::createByTileList($tileList));
        // 0 2
        // 1 3
        static::assertLiveWallDraw($tileList[2], 2, 3, $liveWall);
        static::assertLiveWallDraw($tileList[3], 1, 2, $liveWall);
        static::assertLiveWallDraw($tileList[0], 1, 1, $liveWall);
        static::assertLiveWallDraw($tileList[1], 0, 0, $liveWall);

        $liveWall->init(StackList::createByTileList($tileList));
        $mockNext = Tile::fromString('E');
        $liveWall->debugSetNextDrawTile($mockNext);
        static::assertLiveWallDraw($mockNext, 2, 3, $liveWall);
        $liveWall->debugSetRemainTileCount(1);
        static::assertLiveWallDraw($tileList[1], 0, 0, $liveWall);
        // test deal todo
    }

    static function assertLiveWallDraw(Tile $tile, int $stackCount, int $tileCount, LiveWall $liveWall) {
        static::assertEquals($tile, $liveWall->draw());
        static::assertEquals($stackCount, $liveWall->getRemainStackCount());
        static::assertEquals($tileCount, $liveWall->getRemainTileCount());
    }

    function testWallToJson() {
        $round = $this->getInitRound();
        $wall = $round->getWall();
//        echo json_encode($wall->toJson());
    }
}
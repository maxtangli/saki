<?php

use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Tile\TileSet;
use Saki\Game\Wall\DrawWall;
use Saki\Game\Wall\LiveWall;
use Saki\Game\Wall\Stack;
use Saki\Game\Wall\StackList;

class WallTest extends \SakiTestCase {
    function testStack() {
        $stack = new Stack();
        $t0 = Tile::fromString('1s');
        $t1 = Tile::fromString('2s');

        $stack->setTileChunk([$t0, $t1]);
        $this->assertEquals(['O', 'O'], $stack->toJson());
        $stack->getTop()->open();
        $stack->getBottom()->open();
        $this->assertEquals(['1s', '2s'], $stack->toJson());
        $this->assertEquals($t0, $stack->popTile());
        $this->assertEquals(['X', '2s'], $stack->toJson());
        $this->assertEquals($t1, $stack->popTile());
        $this->assertEquals(['X', 'X'], $stack->toJson());

        $stack->setTileChunk([$t0, $t1]);
        $mockNext = Tile::fromString('E');
        $stack->setNextPopTile($mockNext);
        $this->assertEquals($mockNext, $stack->popTile());
    }

    function testStackList() {
        $tileList = TileList::fromString('0123s');
        $stackList = StackList::fromTileList($tileList);
        $this->assertEquals($tileList, $stackList->toTileList());
    }

    function testStackListBreak() {
        // E       S        W        N
        // 0       1        2        3
        // 0...16, 17...33, 34...50, 51...67
        // e.x. dice 5, last 16, aliveFirst 11,
        //      replace 12...13, indicator 14...18, live 11...0,67...19
        $stackList = StackList::fromTileList(TileSet::createStandard());
        $diceResult = 5;
        $threeBreak = $stackList->toThreeBreak($diceResult);

        $liveStackList = $threeBreak[0];
        $this->assertSame($stackList[11], $liveStackList->getLast());
        $this->assertSame($stackList[19], $liveStackList->getFirst());

        $replaceStackList = $threeBreak[1];
        $this->assertSame($stackList[12], $replaceStackList->getFirst());
        $this->assertSame($stackList[13], $replaceStackList->getLast());

        $indicatorStackList = $threeBreak[2];
        $this->assertSame($stackList[14], $indicatorStackList->getFirst());
        $this->assertSame($stackList[18], $indicatorStackList->getLast());
    }

    function testDrawWall() {
        $drawWall = new LiveWall(true);
        $tileList = TileList::fromString('0123s');
        $drawWall->init(StackList::fromTileList($tileList));
        // 0 2
        // 1 3
        static::assertDrawWallOut($tileList[2], 2, 3, $drawWall);
        static::assertDrawWallOut($tileList[3], 1, 2, $drawWall);
        static::assertDrawWallOut($tileList[0], 1, 1, $drawWall);
        static::assertDrawWallOut($tileList[1], 0, 0, $drawWall);

        $drawWall->init(StackList::fromTileList($tileList));
        $mockNext = Tile::fromString('E');
        $drawWall->debugSetNextTile($mockNext);
        static::assertDrawWallOut($mockNext, 2, 3, $drawWall);
        $drawWall->debugSetRemainTileCount(1);
        static::assertDrawWallOut($tileList[1], 0, 0, $drawWall);
        // test deal: ignore
    }

    static function assertDrawWallOut(Tile $tile, int $stackCount, int $tileCount, LiveWall $liveWall) {
        static::assertEquals($tile, $liveWall->outNext());
        static::assertEquals($stackCount, $liveWall->getRemainStackCount());
        static::assertEquals($tileCount, $liveWall->getRemainTileCount());
    }

    function testActorWall() {
        // todo
    }
}
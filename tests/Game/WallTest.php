<?php

use Saki\Game\Wall;

class WallTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $standardCnt = 136;

        // new
        $w = new Wall(Wall::getStandardTileList());
        $this->assertCount($standardCnt, $w);

        // new by current
        $w = new Wall(Wall::getStandardTileList(), \Saki\TileList::fromString('123s'));
        $this->assertCount(3, $w);
        foreach($w as $k => $v) {
            $this->assertEquals($k+1, $v->getNumber());
        }

        // init
        $w = new Wall(Wall::getStandardTileList(), \Saki\TileList::fromString('123s'));
        $w->init(false);
        $this->assertCount($standardCnt, $w);

        // pop
        $w = new Wall(Wall::getStandardTileList(), \Saki\TileList::fromString('123456789s'));
        $t = $w->pop();
        $this->assertEquals(\Saki\Tile::fromString('9s'), $t);
        $this->assertEquals('12345678s', $w->__toString());

        // pop many
        $w = new Wall(Wall::getStandardTileList(), \Saki\TileList::fromString('123456789s'));
        $ts = $w->popMany(2);
        $this->assertEquals(\Saki\Tile::fromString('9s'), $ts[0]);
        $this->assertEquals(\Saki\Tile::fromString('8s'), $ts[1]);
        $this->assertEquals('1234567s', $w->__toString());

        // shift
        $w = new Wall(Wall::getStandardTileList(), \Saki\TileList::fromString('123456789s'));
        $t = $w->shift();
        $this->assertEquals(\Saki\Tile::fromString('1s'), $t);
        $this->assertEquals('23456789s', $w->__toString());
    }


}

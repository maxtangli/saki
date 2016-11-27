<?php

use Saki\Game\DoraFacade;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Wall\DeadWall;
use Saki\Game\Wall\StackList;

class DeadWallTest extends \SakiTestCase {
    protected function assertTileFan(DoraFacade $f, string $tileString, $doraFan, $uraDoraFan = 0, $redDoraFan = 0) {
        $tile = Tile::fromString($tileString);
        $this->assertEquals($doraFan, $f->getTileDoraFan($tile));
        $this->assertEquals($uraDoraFan, $f->getTileUraDoraFan($tile));
        $this->assertEquals($redDoraFan, $f->getTileRedDoraFan($tile));
        $this->assertEquals($doraFan + $uraDoraFan + $redDoraFan, $f->getTileAllDoraFan($tile));
    }

    protected function assertHandFan(DoraFacade $f, string $handString, $doraFan, $uraDoraFan = 0, $redDoraFan = 0) {
        $hand = TileList::fromString($handString);
        $this->assertEquals($doraFan, $f->getHandDoraFan($hand));
        $this->assertEquals($uraDoraFan, $f->getHandUraDoraFan($hand));
        $this->assertEquals($redDoraFan, $f->getHandRedDoraFan($hand));
        $this->assertEquals($doraFan + $uraDoraFan + $redDoraFan, $f->getHandAllDoraFan($hand));
    }

    function testDoraFacade() {
        /**
         * replacement * 4
         * E W | 1s 1s 2s 3s 4s <- indicator    * 5
         * S N | 1m 1m 2m 3m 4m <- uraIndicator * 5
         */
        $stackList = StackList::fromTileList(TileList::fromString('EEEE1s1m1s1m2s2m3s3m4s4m'));
        $w = new DeadWall($stackList);
        $f = new DoraFacade($w);

        // 1s
        $this->assertCount(1, $f->getIndicatorList());
        $this->assertTileFan($f, '1s', 0);
        $this->assertTileFan($f, '2s', 1);

        // 1s 2s
        $w->openIndicator(1);
        $this->assertCount(2, $f->getIndicatorList());
        $this->assertTileFan($f, '1s', 0);
        $this->assertTileFan($f, '2s', 2);

        // 1s 1s 2s 3s 4s
        $w->openIndicator(3);
        $this->assertCount(5, $f->getIndicatorList());
        $this->assertTileFan($f, '1s', 0);
        $this->assertTileFan($f, '2s', 2);
        $this->assertTileFan($f, '3s', 1);
        $this->assertTileFan($f, '4s', 1);
        $this->assertTileFan($f, '5s', 1);

        // 1s 1s 2s 3s 4s
        // 1m 1m 2m 3m 4m
        $this->assertCount(0, $f->getUraIndicatorList());
        $w->openUraIndicators();
        $this->assertCount(5, $f->getUraIndicatorList());
        $this->assertTileFan($f, '1m', 0, 0);
        $this->assertTileFan($f, '2m', 0, 2);
        $this->assertTileFan($f, '3m', 0, 1);
        $this->assertTileFan($f, '4m', 0, 1);
        $this->assertTileFan($f, '5m', 0, 1);

        // hand
        $this->assertHandFan($f, '222s12345678999p', 6);
        $this->assertHandFan($f, '222m12345678999p', 0, 6);
        $this->assertHandFan($f, '222s222m45678999p', 6, 6);
    }
}
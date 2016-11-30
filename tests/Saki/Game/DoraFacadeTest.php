<?php

use Saki\Game\DoraFacade;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Game\Wall\IndicatorWall;
use Saki\Game\Wall\StackList;

class DoraFacadeTest extends \SakiTestCase {
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
         * 1s 1s 2s 3s 4s <- indicator    * 5
         * 1m 1m 2m 3m 4m <- uraIndicator * 5
         */
        $stackList = StackList::fromTileList(TileList::fromString('1s1m1s1m2s2m3s3m4s4m'));
        $indicatorWall = new IndicatorWall($stackList);
        $doraFacade = new DoraFacade($indicatorWall);

        // 1s
        $this->assertCount(1, $doraFacade->getIndicatorList());
        $this->assertTileFan($doraFacade, '1s', 0);
        $this->assertTileFan($doraFacade, '2s', 1);

        // 1s 2s
        $indicatorWall->openIndicator();
        $this->assertCount(2, $doraFacade->getIndicatorList());
        $this->assertTileFan($doraFacade, '1s', 0);
        $this->assertTileFan($doraFacade, '2s', 2);

        // 1s 1s 2s 3s 4s
        // 1m 1m 2m 3m 4m
        $this->assertCount(0, $doraFacade->getUraIndicatorList());
        $indicatorWall->openIndicator(3);
        $indicatorWall->openUraIndicators();
        $this->assertCount(5, $doraFacade->getUraIndicatorList());
        $this->assertTileFan($doraFacade, '1m', 0, 0);
        $this->assertTileFan($doraFacade, '2m', 0, 2);
        $this->assertTileFan($doraFacade, '3m', 0, 1);
        $this->assertTileFan($doraFacade, '4m', 0, 1);
        $this->assertTileFan($doraFacade, '5m', 0, 1);

        // hand
        $this->assertHandFan($doraFacade, '222s12345678999p', 6);
        $this->assertHandFan($doraFacade, '222m12345678999p', 0, 6);
        $this->assertHandFan($doraFacade, '222s222m45678999p', 6, 6);
    }
}
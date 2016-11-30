<?php

use Saki\Game\Tile\TileList;
use Saki\Game\Wall\DoraType;
use Saki\Game\Wall\IndicatorWall;
use Saki\Game\Wall\StackList;

class IndicatorWallTest extends \SakiTestCase {
    protected function assertHandFan(IndicatorWall $indicatorWall, string $handString, $doraFan, $uraDoraFan = 0, $redDoraFan = 0) {
        $hand = TileList::fromString($handString);
        $this->assertEquals($doraFan, $indicatorWall->getHandDoraFan($hand));
        $this->assertEquals($uraDoraFan, $indicatorWall->getHandUraDoraFan($hand));
        $this->assertEquals($redDoraFan, DoraType::create(DoraType::RED_DORA)->getHandFan($hand));
    }

    function testDoraFacade() {
        /**
         * 1s 1s 2s 3s 4s <- indicator    * 5
         * 1m 1m 2m 3m 4m <- uraIndicator * 5
         */
        $stackList = StackList::fromTileList(TileList::fromString('1s1m1s1m2s2m3s3m4s4m'));
        $indicatorWall = new IndicatorWall($stackList);

        // 1s
        $this->assertCount(1, $indicatorWall->getIndicatorList());
        $this->assertHandFan($indicatorWall, '1s', 0);
        $this->assertHandFan($indicatorWall, '2s', 1);

        // 1s 2s
        $indicatorWall->openIndicator();
        $this->assertCount(2, $indicatorWall->getIndicatorList());
        $this->assertHandFan($indicatorWall, '1s', 0);
        $this->assertHandFan($indicatorWall, '2s', 2);

        // 1s 1s 2s 3s 4s
        // 1m 1m 2m 3m 4m
        $this->assertCount(0, $indicatorWall->getUraIndicatorList());
        $indicatorWall->openIndicator(3);
        $indicatorWall->openUraIndicators();
        $this->assertCount(5, $indicatorWall->getUraIndicatorList());
        $this->assertHandFan($indicatorWall, '1m', 0, 0);
        $this->assertHandFan($indicatorWall, '2m', 0, 2);
        $this->assertHandFan($indicatorWall, '3m', 0, 1);
        $this->assertHandFan($indicatorWall, '4m', 0, 1);
        $this->assertHandFan($indicatorWall, '5m', 0, 1);

        // hand
        $this->assertHandFan($indicatorWall, '222s12345678999p', 6);
        $this->assertHandFan($indicatorWall, '222m12345678999p', 0, 6);
        $this->assertHandFan($indicatorWall, '222s222m45678999p', 6, 6);
    }
}
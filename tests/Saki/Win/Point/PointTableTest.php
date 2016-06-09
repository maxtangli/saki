<?php

use Saki\Win\Point\FanAndFu;
use Saki\Win\Point\PointTable;

class PointTableTest extends \SakiTestCase {
    function testDealerSample() {
        $result = PointTable::create()->getDealerSample();
        $var_export = var_export($result, true);
        $file = __DIR__ . '/dealerSample.txt';
//        file_put_contents($file, $var_export);
//        $this->assertEquals(file_get_contents($file), $var_export);
    }

    function testLeisureSample() {
        $result = PointTable::create()->getLeisureSample();
        $var_export = var_export($result, true);
        $file = __DIR__ . '/leisureSample.txt';
//        file_put_contents($file, $var_export);
//        $this->assertEquals(file_get_contents($file), $var_export);
    }

    function testItem() {
        $fanAndFu = new FanAndFu(1, 40);
        $item = PointTable::create()->getPointItem($fanAndFu);
        // dealer lose
        $this->assertEquals(-700, $item->getLoserPointChange(true, false, true));
        $this->assertEquals(-1300, $item->getLoserPointChange(false, false, true));
        // leisure lose
        $this->assertEquals(-700, $item->getLoserPointChange(true, true, false));
        $this->assertEquals(-400, $item->getLoserPointChange(true, false, false));
        $this->assertEquals(-2000, $item->getLoserPointChange(false, true, false));
        $this->assertEquals(-1300, $item->getLoserPointChange(false, false, false));

        // dealer win
        $this->assertEquals(2100, $item->getWinnerPointChange(true, true));
        $this->assertEquals(2000, $item->getWinnerPointChange(false, true));
        // leisure win
        $this->assertEquals(1500, $item->getWinnerPointChange(true, false));
        $this->assertEquals(1300, $item->getWinnerPointChange(false, false));
    }
}

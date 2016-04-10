<?php

use Saki\RoundResult\PointTable;

class PointTableTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider payPointProvider
     */
    function testPayPoint($expectedPayPoint, $receiverIsDealer, $fan, $fu, $winBySelf, $payerIsDealer) {
        $table = PointTable::create();
        $item = $table->getPointItem($fan, $fu);
        $payPoint = $item->getPayPoint($receiverIsDealer, $winBySelf, $payerIsDealer);
        $this->assertEquals($expectedPayPoint, $payPoint);
    }

    function payPointProvider() {
        return [
            // $expectedPayPoint, $receiverIsDealer, $fan, $fu, $winBySelf, $payerIsDealer
            // non-dealer 1 fan 30 fu
            [1000, false, 1, 30, false, false],
            [300, false, 1, 30, true, false],
            [500, false, 1, 30, true, true],
            // dealer 1 fan 30 fu
            [1500, true, 1, 30, false, false],
            [500, true, 1, 30, true, false],
            // non-dealer 3 fan 60 fu
            // non-dealer 3 fan 70 fu
            // non-dealer 4 fan 30 fu
            // non-dealer 4 fan 40 fu
            // non-dealer 5/6/7/8/9/10/11/12/13 fan
        ];
    }
}

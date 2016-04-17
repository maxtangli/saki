<?php

use Saki\Game\SeatWind;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Result\NewWinResult;
use Saki\Win\Result\WinResultInput;

class NewWinResultTest extends SakiTestCase {
    /**
     * @param NewWinResult $result
     * @param string $actorString
     * @param int $tableChange
     * @param int $reachChange
     * @param int $seatChange
     */
    protected function assertPointChange(
        int $tableChange, int $reachChange, int $seatChange,
        NewWinResult $result, string $actorString
    ) {
        $actor = SeatWind::fromString($actorString);
        $this->assertEquals($tableChange, $result->getTablePointChange($actor));
        $this->assertEquals($reachChange, $result->getReachPointsChange($actor));
        $this->assertEquals($seatChange, $result->getSeatWindTurnPointChange($actor));
        $this->assertEquals($tableChange + $reachChange + $seatChange, $result->getPointChange($actor));
    }

    /**
     * @param array $expected
     * @param NewWinResult $result
     */
    protected function assertAllPointChange(array $expected, NewWinResult $result) {
        foreach ($expected as $i => list($tableChange, $reachChange, $seatChange)) {
            $actor = SeatWind::fromIndex($i + 1);
            $this->assertPointChange($tableChange, $reachChange, $seatChange,
                $result, $actor);
        }
    }

    function testDealerWinBySelf() {
        $fanAndFu = new FanAndFu(1, 40);
        $input = WinResultInput::createWinBySelf(
            [SeatWind::createEast(), $fanAndFu],
            [SeatWind::createSouth(), SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1
        );
        $result = new NewWinResult($input);
        $this->assertAllPointChange([
            [2100, 1000, 300],
            [-700, 0, -100],
            [-700, 0, -100],
            [-700, 0, -100],
        ], $result);
    }

    function testDealerWinByOther() {
    }

    function testLeisureWinBySelf() {
    }

    function testLeisureWinByOther() {
    }

    function testMultipleWinByOther() {
    }
}
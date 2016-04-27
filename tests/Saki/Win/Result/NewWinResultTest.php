<?php

use Saki\Game\SeatWind;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;

class NewWinResultTest extends SakiTestCase {
    /**
     * @param WinResult $result
     * @param string $actorString
     * @param int $tableChange
     * @param int $riichiChange
     * @param int $seatChange
     */
    protected function assertPointChange(
        int $tableChange, int $riichiChange, int $seatChange,
        WinResult $result, string $actorString
    ) {
        $actor = SeatWind::fromString($actorString);
        $this->assertEquals($tableChange, $result->getTableChange($actor));
        $this->assertEquals($riichiChange, $result->getRiichiChange($actor));
        $this->assertEquals($seatChange, $result->getSeatChange($actor));

        $totalChange = $tableChange + $riichiChange + $seatChange;
        $this->assertEquals($totalChange, $result->getPointChange($actor));

        $this->assertEquals($totalChange, $result->getPointChangeMap()[$actor->__toString()]);
    }

    /**
     * @param array $expected
     * @param WinResult $result
     */
    protected function assertAllPointChange(array $expected, WinResult $result) {
        foreach ($expected as $i => list($tableChange, $riichiChange, $seatChange)) {
            $actor = SeatWind::fromIndex($i + 1);
            $this->assertPointChange($tableChange, $riichiChange, $seatChange,
                $result, $actor);
        }
    }

    function testDealerTsumo() {
        $input = WinResultInput::createTsumo(
            [SeatWind::createEast(), new FanAndFu(1, 40)],
            [SeatWind::createSouth(), SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2100, 1000, 300],
            [-700, 0, -100],
            [-700, 0, -100],
            [-700, 0, -100],
        ], $result);
    }

    function testDealerRon() {
        $input = WinResultInput::createRon(
            [[SeatWind::createEast(), new FanAndFu(1, 40)]],
            SeatWind::createSouth(),
            [SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2000, 1000, 300],
            [-2000, 0, -300],
            [0, 0, 0],
            [0, 0, 0],
        ], $result);
    }

    function testLeisureTsumo() {
        $input = WinResultInput::createTsumo(
            [SeatWind::createSouth(), new FanAndFu(2, 40)],
            [SeatWind::createEast(), SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [-1300, 0, -100],
            [2700, 1000, 300],
            [-700, 0, -100],
            [-700, 0, -100],
        ], $result);
    }

    function testLeisureRon() {
        $input = WinResultInput::createRon(
            [[SeatWind::createSouth(), new FanAndFu(2, 40)]],
            SeatWind::createWest(),
            [SeatWind::createEast(), SeatWind::createNorth()],
            1000,
            1
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [0, 0, 0],
            [2600, 1000, 300],
            [-2600, 0, -300],
            [0, 0, 0],
        ], $result);
    }

    function testMultipleRon() {
        $input = WinResultInput::createRon(
            [[SeatWind::createEast(), new FanAndFu(1, 40)], [SeatWind::createSouth(), new FanAndFu(2, 40)]],
            SeatWind::createWest(),
            [SeatWind::createNorth()],
            1000,
            1
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2000, 1000, 300],
            [2600, 0, 300],
            [-2000 - 2600, 0, -300 - 300],
            [0, 0, 0],
        ], $result);
    }
}
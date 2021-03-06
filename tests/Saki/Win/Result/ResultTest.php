<?php

use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\Pao\Pao;
use Saki\Win\Pao\PaoList;
use Saki\Win\Pao\PaoType;
use Saki\Win\Point\FanAndFu;
use Saki\Win\Result\ExhaustiveDrawResult;
use Saki\Win\Result\Result;
use Saki\Win\Result\WinResult;
use Saki\Win\Result\WinResultInput;

class WinResultTest extends \SakiTestCase {
    /**
     * @param WinResult $result
     * @param string $actorString
     * @param int $tableChange
     * @param int $riichiChange
     * @param int $seatChange
     */
    private function assertWinResultPointChange(
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

    private function assertResultPointChange(int $pointChange, string $seatWind, Result $result) {
        $actual = $result->getPointChange(SeatWind::fromString($seatWind));
        $this->assertEquals($pointChange, $actual);
    }

    /**
     * @param array $expected
     * @param WinResult $result
     */
    private function assertAllPointChange(array $expected, WinResult $result) {
        foreach ($expected as $i => list($tableChange, $riichiChange, $seatChange)) {
            $actor = SeatWind::fromIndex($i + 1);
            $this->assertWinResultPointChange($tableChange, $riichiChange, $seatChange,
                $result, $actor);
        }
    }

    /**
     * @param array $a
     * @return PaoList
     */
    private function getPaoList(array $a) {
        $createPao = function (array $a) {
            list($from, $to) = $a;
            return new Pao(SeatWind::fromString($from),
                SeatWind::fromString($to),
                PaoType::create(PaoType::BIG_THREE_DRAGONS_PAO));
        };
        return (new PaoList())->fromSelect(
            (new ArrayList($a))->select($createPao)
        );
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

        $input = WinResultInput::createTsumo(
            [SeatWind::createEast(), new FanAndFu(1, 40)],
            [SeatWind::createSouth(), SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1,
            null,
            $this->getPaoList([['S', 'E']])
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2100, 1000, 300],
            [-2100, 0, -300],
            [0, 0, 0],
            [0, 0, 0],
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

        $input = WinResultInput::createRon(
            [[SeatWind::createEast(), new FanAndFu(1, 40)]],
            SeatWind::createSouth(),
            [SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1,
            null,
            $this->getPaoList([['W', 'E']])
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2000, 1000, 300],
            [-1000, 0, -150],
            [-1000, 0, -150],
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

        $input = WinResultInput::createTsumo(
            [SeatWind::createSouth(), new FanAndFu(2, 40)],
            [SeatWind::createEast(), SeatWind::createWest(), SeatWind::createNorth()],
            1000,
            1,
            null,
            $this->getPaoList([['W', 'S']])
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [0, 0, 0],
            [2700, 1000, 300],
            [-2700, 0, -300],
            [0, 0, 0],
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

        $input = WinResultInput::createRon(
            [[SeatWind::createSouth(), new FanAndFu(2, 40)]],
            SeatWind::createWest(),
            [SeatWind::createEast(), SeatWind::createNorth()],
            1000,
            1,
            null,
            $this->getPaoList([['E', 'S']])
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [-1300, 0, -150],
            [2600, 1000, 300],
            [-1300, 0, -150],
            [0, 0, 0],
        ], $result);
    }

    function testDoubleRon() {
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

        $input = WinResultInput::createRon(
            [[SeatWind::createEast(), new FanAndFu(1, 40)], [SeatWind::createSouth(), new FanAndFu(2, 40)]],
            SeatWind::createWest(),
            [SeatWind::createNorth()],
            1000,
            1,
            null,
            $this->getPaoList([['N', 'S']])
        );
        $result = new WinResult($input);
        $this->assertAllPointChange([
            [2000, 1000, 300],
            [2600, 0, 300],
            [-2000 - 1300, 0, -300 - 150],
            [0 - 1300, 0, 0 - 150],
        ], $result);
    }

    /**
     * @param array $pointChanges
     * @param array $waitingArray
     * @dataProvider provideExhaustiveResult
     */
    function testExhaustiveResult(array $pointChanges, array $waitingArray) {
        $keys = PlayerType::create(count($waitingArray))->getSeatWindList()->toArray();
        $waitingMap = array_combine($keys, $waitingArray);
        $result = new ExhaustiveDrawResult($waitingMap);
        $this->assertResultPointChange($pointChanges[0], 'E', $result);
        $this->assertResultPointChange($pointChanges[1], 'S', $result);
        $this->assertResultPointChange($pointChanges[2], 'W', $result);
        $this->assertResultPointChange($pointChanges[3], 'N', $result);
    }

    function provideExhaustiveResult() {
        return [
            [[0, 0, 0, 0], [false, false, false, false]],
            [[3000, -1000, -1000, -1000], [true, false, false, false]],
            [[1500, 1500, -1500, -1500], [true, true, false, false]],
            [[1000, 1000, 1000, -3000], [true, true, true, false]],
            [[0, 0, 0, 0], [true, true, true, true]],
        ];
    }
}
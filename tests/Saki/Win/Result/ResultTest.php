<?php

use Saki\Win\Result\WinResult;
use Saki\Win\WinReport;
use Saki\Win\WinState;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\YakuItem;
use Saki\Win\Yaku\YakuItemList;

// todo refactor to kiss
class ResultTest extends SakiTestCase {
    protected function assertFanAndFu(int $expectedFan, int $expectedFu, WinReport $winReport) {
        $this->assertEquals($expectedFan, $winReport->getFan());
        $this->assertEquals($expectedFu, $winReport->getFu());
    }

    protected function assertPoints(array $expected, WinReport $winReport) {
        
    }

    protected function get3FanYakuList() {
        $yakuList = new YakuItemList([
            new YakuItem(AllRunsYaku::create(), 1),
            new YakuItem(RedValueTilesYaku::create(), 1),
            new YakuItem(GreenValueTilesYaku::create(), 1)
        ]);
        return $yakuList;
    }

    function testTsumo() {
        $round = $this->getInitRound();
        $playerList = $round->getPlayerList();
        $players = $playerList->toArray();

        $yakuList = $this->get3FanYakuList();
        $winResult = new WinReport(WinState::create(WinState::WIN_BY_SELF), $yakuList, 40);
        $this->assertFanAndFu(3, 40, $winResult);



        // 40符3番 親 7700 / 2600all,
        $r = WinResult::createTsumo($players, $playerList[0], $winResult, 0, 0);
        $expected = [
            [$playerList[0], 2600 * 3],
            [$playerList[1], -2600],
            [$playerList[2], -2600],
            [$playerList[3], -2600],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }

        // accumulatedReachCount + seatWindTurn
        $r = WinResult::createTsumo($players, $playerList[0], $winResult, 2, 2);
        $expected = [
            [$playerList[0], 2600 * 3 + 2000 + 600],
            [$playerList[1], -2600 - 200],
            [$playerList[2], -2600 - 200],
            [$playerList[3], -2600 - 200],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }

        // 40符3番 子 5200 / 1300+1300+2600
        $r = WinResult::createTsumo($players, $playerList[1], $winResult, 0, 0);
        $expected = [
            [$playerList[0], -2600],
            [$playerList[1], 2600 + 1300 + 1300],
            [$playerList[2], -1300],
            [$playerList[3], -1300],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p));
        }
    }

    function testRon() {
        $r = $this->getInitRound();
        $playerList = $r->getPlayerList();
        $players = $playerList->toArray();
        $yakuList = $this->get3FanYakuList();
        $winResult = new WinReport(WinState::create(WinState::WIN_BY_OTHER), $yakuList, 40);

        $this->assertFanAndFu(3, 40, $winResult);

        // 40符3番 親 7700 / 2600all,
        $r = WinResult::createRon($players, $players[0], $winResult, $players[1], 0, 0);
        $expected = [
            [$playerList[0], 7700],
            [$playerList[1], -7700],
            [$playerList[2], 0],
            [$playerList[3], 0],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }

        // 2,2
        $r = WinResult::createRon($players, $players[0], $winResult, $players[1], 2, 2);
        $expected = [
            [$playerList[0], 7700 + 2000 + 600],
            [$playerList[1], -7700 - 600],
            [$playerList[2], 0],
            [$playerList[3], 0],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }

        // 40符3番 子 5200 / 1300+1300+2600
        $r = WinResult::createRon($players, $players[1], $winResult, $players[0], 0, 0);
        $expected = [
            [$playerList[0], -5200],
            [$playerList[1], 5200],
            [$playerList[2], 0],
            [$playerList[3], 0],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p));
        }
    }

    function testMultiRon() {
        $r = $this->getInitRound();
        $playerList = $r->getPlayerList();
        $players = $playerList->toArray();
        $yakuList = $this->get3FanYakuList();
        $winResult = new WinReport(WinState::create(WinState::WIN_BY_OTHER), $yakuList, 40);

        $this->assertFanAndFu(3, 40, $winResult);

        // 40符3番 親 7700, 子 5200
        $r = WinResult::createMultiRon($players, [$players[0], $players[1]], [$winResult, $winResult], $players[3], 0, 0);
        $expected = [
            [$playerList[0], 7700],
            [$playerList[1], 5200],
            [$playerList[2], 0],
            [$playerList[3], -7700 - 5200],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }

        // 40符3番 親 7700, 子 5200
        $r = WinResult::createMultiRon($players, [$players[0], $players[1]], [$winResult, $winResult], $players[3], 2, 2);
        $expected = [
            [$playerList[0], 7700 + 1000 + 600],
            [$playerList[1], 5200 + 1000 + 600],
            [$playerList[2], 0],
            [$playerList[3], -7700 - 5200 - 1200],
        ];
        foreach ($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getPointDeltaInt($p), $p->getNo());
        }
    }
}

<?php

use Saki\Game\Round;
use Saki\RoundResult\WinRoundResult;
use Saki\Win\Yaku\Fan1\AllRunsYaku;
use Saki\Win\Yaku\Fan1\GreenValueTilesYaku;
use Saki\Win\Yaku\Fan1\RedValueTilesYaku;
use Saki\Win\Yaku\YakuItem;
use Saki\Win\Yaku\YakuItemList;

// todo refactor to kiss
class WinRoundResultTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        $r = new Round();
        $playerList = $r->getPlayerList();
        $players = $playerList->toArray();
        $yakuList = new YakuItemList(
            [
                new YakuItem(AllRunsYaku::create(), 1),
                new YakuItem(RedValueTilesYaku::create(), 1),
                new YakuItem(GreenValueTilesYaku::create(), 1)
            ]
        );
        $winResult = new \Saki\Win\WinResult(\Saki\Win\WinState::create(\Saki\Win\WinState::WIN_BY_SELF), $yakuList, 40);

        $this->assertEquals(3, $winResult->getFan());
        $this->assertEquals(40, $winResult->getFu());

        // 40符3番 親 7700 / 2600all,
        $r = WinRoundResult::createWinBySelf($players, $playerList[0], $winResult, 0, 0);
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
        $r = WinRoundResult::createWinBySelf($players, $playerList[0], $winResult, 2, 2);
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
        $r = WinRoundResult::createWinBySelf($players, $playerList[1], $winResult, 0, 0);
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

    function testWinByOther() {
        $r = new Round();
        $playerList = $r->getPlayerList();
        $players = $playerList->toArray();
        $yakuList = new YakuItemList(
            [
                new YakuItem(AllRunsYaku::create(), 1),
                new YakuItem(RedValueTilesYaku::create(), 1),
                new YakuItem(GreenValueTilesYaku::create(), 1)
            ]
        );
        $winResult = new \Saki\Win\WinResult(\Saki\Win\WinState::create(\Saki\Win\WinState::WIN_BY_OTHER), $yakuList, 40);

        $this->assertEquals(3, $winResult->getFan());
        $this->assertEquals(40, $winResult->getFu());

        // 40符3番 親 7700 / 2600all,
        $r = WinRoundResult::createWinByOther($players, $players[0], $winResult, $players[1], 0, 0);
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
        $r = WinRoundResult::createWinByOther($players, $players[0], $winResult, $players[1], 2, 2);
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
        $r = WinRoundResult::createWinByOther($players, $players[1], $winResult, $players[0], 0, 0);
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

    function testMultiWinByOther() {
        $r = new Round();
        $playerList = $r->getPlayerList();
        $players = $playerList->toArray();
        $yakuList = new YakuItemList(
            [
                new YakuItem(AllRunsYaku::create(), 1),
                new YakuItem(RedValueTilesYaku::create(), 1),
                new YakuItem(GreenValueTilesYaku::create(), 1)
            ]
        );
        $winResult = new \Saki\Win\WinResult(\Saki\Win\WinState::create(\Saki\Win\WinState::WIN_BY_OTHER), $yakuList, 40);

        $this->assertEquals(3, $winResult->getFan());
        $this->assertEquals(40, $winResult->getFu());

        // 40符3番 親 7700, 子 5200
        $r = WinRoundResult::createMultiWinByOther($players, [$players[0], $players[1]], [$winResult, $winResult], $players[3], 0, 0);
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
        $r = WinRoundResult::createMultiWinByOther($players, [$players[0], $players[1]], [$winResult, $winResult], $players[3], 2, 2);
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

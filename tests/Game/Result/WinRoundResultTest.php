<?php

class WinRoundResultTest extends PHPUnit_Framework_TestCase {
    function testWinBySelf() {
        $playerList = \Saki\Game\PlayerList::getStandardPlayerList();
        $players = $playerList->toArray();
        $yakuList = new \Saki\Yaku\YakuList(
            [\Saki\Yaku\AllRunsYaku::getInstance(),
            \Saki\Yaku\RedValueTilesYaku::getInstance(),
            \Saki\Yaku\GreenValueTilesYaku::getInstance()],
            false);
        $winResult = new \Saki\Win\WinAnalyzerResult(\Saki\Win\WinState::getWinInstance(), $yakuList, 40);

        $this->assertEquals(3, $winResult->getFanCount());
        $this->assertEquals(40, $winResult->getFuCount());

        // 40符3番 親 7700 / 2600all,
        $r = new \Saki\Game\Result\WinBySelfRoundResult($players, $playerList[0], $winResult, 0, 0);
        $expected = [
            [$playerList[0], 2600*3],
            [$playerList[1], -2600],
            [$playerList[2], -2600],
            [$playerList[3], -2600],
        ];
        foreach($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getScoreDeltaInt($p), $p->getNo());
        }

        // 40符3番 子 5200 / 1300+1300+2600
        $r = new \Saki\Game\Result\WinBySelfRoundResult($players, $playerList[1], $winResult, 0, 0);
        $expected = [
            [$playerList[0], -2600],
            [$playerList[1], 2600+1300+1300],
            [$playerList[2], -1300],
            [$playerList[3], -1300],
        ];
        foreach($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getScoreDeltaInt($p));
        }
    }

    function testWinByOther() {
        $playerList = \Saki\Game\PlayerList::getStandardPlayerList();
        $players = $playerList->toArray();
        $yakuList = new \Saki\Yaku\YakuList(
            [\Saki\Yaku\AllRunsYaku::getInstance(),
                \Saki\Yaku\RedValueTilesYaku::getInstance(),
                \Saki\Yaku\GreenValueTilesYaku::getInstance()],
            false);
        $winResult = new \Saki\Win\WinAnalyzerResult(\Saki\Win\WinState::getWinInstance(), $yakuList, 40);

        $this->assertEquals(3, $winResult->getFanCount());
        $this->assertEquals(40, $winResult->getFuCount());

        // 40符3番 親 7700 / 2600all,
        $r = new \Saki\Game\Result\WinByOtherRoundResult($players, $players[0], $winResult, $players[1], 0, 0);
        $expected = [
            [$playerList[0], 7700],
            [$playerList[1], -7700],
            [$playerList[2], 0],
            [$playerList[3], 0],
        ];
        foreach($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getScoreDeltaInt($p), $p->getNo());
        }

        // 40符3番 子 5200 / 1300+1300+2600
        $r = new \Saki\Game\Result\WinByOtherRoundResult($players, $players[1], $winResult, $players[0], 0, 0);
        $expected = [
            [$playerList[0], -5200],
            [$playerList[1], 5200],
            [$playerList[2], 0],
            [$playerList[3], 0],
        ];
        foreach($expected as list($p, $deltaInt)) {
            $this->assertEquals($deltaInt, $r->getScoreDeltaInt($p));
        }
    }

    function testMultiWinByOther() {
        // todo
    }
}

<?php

use Saki\Game\Round;
use Saki\Game\PlayerList;
use Saki\Game\Wall;

class RoundTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $playerList = new PlayerList(PlayerList::createPlayers(3, 40000));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];
        $r = new Round($wall, $playerList, $dealerPlayer);

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $r->getPlayerArea($player)->getOnHandTileOrderedList();
            $expectedCnt = $player == $r->getDealerPlayer() ? 17 : 16;
            $this->assertCount($expectedCnt, $onHandTileList);
        }
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getTurnManager()->getCurrentPlayer());
    }
}

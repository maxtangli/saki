<?php

class RoundTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $wall = new \Saki\Game\Wall(\Saki\Game\Wall::getStandardTileList());
        $players = ['saki', 'nodoka', 'koromo'];
        $currentPlayer = 'koromo';
        $r = new \Saki\Game\Round($wall, $players, $currentPlayer);

        // initial on-hand tile count
        foreach ($r->getPlayers() as $player) {
            $onHandTileList = $r->getPlayerArea($player)->getOnHandTileOrderedList();
            $expectedCnt = $player == $r->getDealerPlayer() ? 17 : 16;
            $this->assertCount($expectedCnt, $onHandTileList);
        }
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getTurnManager()->getCurrentPlayer());
    }
}

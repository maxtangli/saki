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
            $onHandTileList = $r->getPlayerArea($player)->getOnHandTileSortedList();
            $this->assertCount(16, $onHandTileList);
        }
        // initial candidate tile
        $this->assertNotNull($r->getPlayerArea($r->getDealerPlayer())->getCandidateTile());
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getCurrentPlayer());

        // candidate commands
        $candidateCommands = $r->getCandidateCommands();
        foreach($candidateCommands as $command) {
            $this->assertEquals($r->getCurrentPlayer(), $command->getPlayer());
        }
        $this->assertGreaterThan(0, count($candidateCommands));

        // execute command
        $discardCommand = $candidateCommands[0];
        $firstOnHandTile = $r->getPlayerArea($r->getCurrentPlayer())->getOnHandTileSortedList()[0];
        $this->assertEquals($firstOnHandTile, $discardCommand->getTile());

        $r->acceptCommand($discardCommand);
    }

    function testSerialize() {
        $playerList = new PlayerList(PlayerList::createPlayers(3, 40000));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];
        $r = new Round($wall, $playerList, $dealerPlayer);
        $currentTurn = $r->getCurrentTurn();
        $r2 = unserialize(serialize($r));
        $this->assertEquals($currentTurn, $r2->getCurrentTurn());
        $this->assertEquals($r, $r2);
    }
}

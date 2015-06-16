<?php

use Saki\Game\Round;
use Saki\Game\PlayerList;
use Saki\Game\Wall;
use Saki\Command\DiscardCommand;

class RoundTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Round
     */
    protected $round;

    protected function setUp() {
        $playerList = new PlayerList(PlayerList::createPlayers(3, 40000));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];
        $this->round = new Round($wall, $playerList, $dealerPlayer);
    }

    function testInit() {
        $r = $this->round;

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $r->getPlayerArea($player)->getOnHandTileSortedList();
            $this->assertCount(16, $onHandTileList);
        }
        // initial candidate tile
        $this->assertNotNull($r->getPlayerArea($r->getDealerPlayer())->getCandidateTile());
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getCurrentPlayer());
    }

    function testCandidateCommands() {
        $r = $this->round;

        // candidate commands
        $candidateCommands = $r->getCandidateCommands();
        foreach ($candidateCommands as $command) {
            $this->assertEquals($r->getCurrentPlayer(), $command->getPlayer());
        }
        $this->assertGreaterThan(0, count($candidateCommands));

        // execute command
        $discardCommand = $candidateCommands[0];
        $firstOnHandTile = $r->getPlayerArea($r->getCurrentPlayer())->getOnHandTileSortedList()[0];
        $this->assertEquals($firstOnHandTile, $discardCommand->getTile());

        $r->acceptCommand($discardCommand);
    }

    function testExhaustiveDraw() {
        $r = $this->round;
        $this->assertNotEquals(\Saki\Game\RoundPhase::OVER_PHASE, $r->getRoundPhase()->getValue());

        while(!empty($r->getCandidateCommands())) {
            $command = $r->getCandidateCommands()[0];
            $this->assertInstanceOf('Saki\Command\DiscardCommand', $command);
            $r->acceptCommand($command);
        }
        $this->assertEquals(\Saki\Game\RoundPhase::OVER_PHASE, $r->getRoundPhase()->getValue());
    }

    function testSerialize() {
        $r = $this->round;
        $currentTurn = $r->getCurrentTurn();
        $r2 = unserialize(serialize($r));
        $this->assertEquals($currentTurn, $r2->getCurrentTurn());
        $this->assertEquals($r, $r2);
    }
}

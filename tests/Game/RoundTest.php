<?php

use Saki\Game\Round;
use Saki\Game\PlayerList;
use Saki\Game\Wall;
use Saki\Command\DiscardCommand;
use Saki\Tile;
use Saki\Meld\Meld;
use Saki\TileList;
use Saki\Game\RoundPhase;

class RoundTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Round
     */
    protected $round;
    /**
     * @var Round
     */
    protected $roundAfterDiscard1m;

    protected function setUp() {
        $playerList = new PlayerList(PlayerList::createPlayers(3, 40000));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];

        $this->round = new Round($wall, $playerList, $dealerPlayer);

        $r = new Round($wall, $playerList, $dealerPlayer);
        $discardPlayer = $r->getCurrentPlayer();
        $r->getPlayerArea($discardPlayer)->getHandTileSortedList()->replaceByIndex(0, \Saki\Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE), $r->getRoundPhase());
        $this->assertEquals(1, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());
        $this->roundAfterDiscard1m = $r;
    }

    function testInit() {
        $r = $this->round;

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $r->getPlayerArea($player)->getHandTileSortedList();
            $expected = $player==$r->getDealerPlayer() ? 14 : 13;
            $this->assertCount($expected, $onHandTileList);
        }
        // initial candidate tile
        $this->assertNotNull($r->getPlayerArea($r->getDealerPlayer())->getCandidateTile());
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getCurrentPlayer());
    }

//
//    function testCandidateCommands() {
//        $r = $this->round;
//
//        // candidate commands
//        $candidateCommands = $r->getCandidateCommands();
//        foreach ($candidateCommands as $command) {
//            $this->assertEquals($r->getCurrentPlayer(), $command->getPlayer());
//        }
//        $this->assertGreaterThan(0, count($candidateCommands));
//
//        // execute command
//        $discardCommand = $candidateCommands[0];
//        $firstOnHandTile = $r->getPlayerArea($r->getCurrentPlayer())->getHandTileSortedList()[0];
//        $this->assertEquals($firstOnHandTile, $discardCommand->getTile());
//
//        $r->acceptCommand($discardCommand);
//    }
//
//    function testExhaustiveDraw() {
//        $r = $this->round;
//        $this->assertNotEquals(\Saki\Game\RoundPhase::OVER_PHASE, $r->getRoundPhase()->getValue());
//
//        while(!empty($r->getCandidateCommands())) {
//            $command = $r->getCandidateCommands()[0];
//            $this->assertInstanceOf('Saki\Command\DiscardCommand', $command);
//            $r->acceptCommand($command);
//        }
//        $this->assertEquals(\Saki\Game\RoundPhase::OVER_PHASE, $r->getRoundPhase()->getValue());
//    }


    function testKongBySelf() {
        $r = $this->round;
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayerArea()->getHandTileSortedList()->replaceByIndex([0,1,2,3],
            [Tile::fromString('1m'), Tile::fromString('1m'),Tile::fromString('1m'),Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getCurrentPlayerArea()->getHandTileSortedList()->count();
        $r->kongBySelf($r->getCurrentPlayer(),Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore - 3, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('(1111m)')));
    }

    function testPlusKongBySelf() {
        $r = $this->round;
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayerArea()->getHandTileSortedList()->replaceByIndex([0],
            [Tile::fromString('1m')]);
        $r->getCurrentPlayerArea()->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $r->getCurrentPlayerArea()->getHandTileSortedList()->count();
        $r->plusKongBySelf($r->getCurrentPlayer(),Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
    }

    function testChowByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextPlayer();
        $r->getPlayerArea($actPlayer)->getHandTileSortedList()->replaceByIndex([0,1],[Tile::fromString('2m'), Tile::fromString('3m')]);
        // execute
        $tileCountBefore = $r->getPlayerArea($actPlayer)->getHandTileSortedList()->count();
        $r->chowByOther($actPlayer, Tile::fromString('2m'), Tile::fromString('3m'));
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($prePlayer)->getDiscardedTileList()->count());
    }

    function testPongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $r->getPlayerArea($actPlayer)->getHandTileSortedList()->replaceByIndex([0,1],[Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getPlayerArea($actPlayer)->getHandTileSortedList()->count();
        $r->pongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($prePlayer)->getDiscardedTileList()->count());
    }

    function testKongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $r->getPlayerArea($actPlayer)->getHandTileSortedList()->replaceByIndex([0,1,2],[Tile::fromString('1m'), Tile::fromString('1m'),Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getPlayerArea($actPlayer)->getHandTileSortedList()->count();
        $r->kongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($prePlayer)->getDiscardedTileList()->count());
    }

    function testPlusKongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $r->getPlayerArea($actPlayer)->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $r->getPlayerArea($actPlayer)->getHandTileSortedList()->count();
        $r->plusKongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertFalse($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertTrue($r->getCurrentPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore+1, $r->getCurrentPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($prePlayer)->getDiscardedTileList()->count());
    }
}

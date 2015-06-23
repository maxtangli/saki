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

    function testSerialize() {
        $r = $this->round;
        $r2 = unserialize(serialize($r));
        $this->assertEquals($r, $r2);
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
//        $firstOnHandTile = $r->getPlayerArea($r->getCurrentPlayer())->getOnHandTileSortedList()[0];
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

    function testChow() {
        $r = $this->round;
        // discardPlayer vs chowPlayer
        $discardPlayer = $r->getCurrentPlayer();
        $chowPlayer = $r->getNextPlayer();

        // discard
        $r->getPlayerArea($discardPlayer)->getOnHandTileSortedList()->replaceByIndex(0, \Saki\Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE), $r->getRoundPhase());
        $this->assertEquals(1, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());

        // chow
        $chowPlayerTileCountBefore = $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->count();
        $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->replaceByIndex([0,1],[Tile::fromString('2m'), Tile::fromString('3m')]);
        $r->chow($r->getNextPlayer(), Tile::fromString('2m'), Tile::fromString('3m'));

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($chowPlayer, $r->getCurrentPlayer());
        $this->assertTrue($r->getCurrentPlayerArea()->getExposedMeldList()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($chowPlayerTileCountBefore - 2, $r->getCurrentPlayerArea()->getOnHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());
    }

    function testPong() {
        $r = $this->round;
        // discardPlayer vs chowPlayer
        $discardPlayer = $r->getCurrentPlayer();
        $chowPlayer = $r->getNextPlayer();

        // discard
        $r->getPlayerArea($discardPlayer)->getOnHandTileSortedList()->replaceByIndex(0, \Saki\Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE), $r->getRoundPhase());
        $this->assertEquals(1, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());

        // pong
        $chowPlayerTileCountBefore = $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->count();
        $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->replaceByIndex([0,1],[Tile::fromString('1m'), Tile::fromString('1m')]);
        $r->pong($r->getNextPlayer());

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($chowPlayer, $r->getCurrentPlayer());
        $this->assertTrue($r->getCurrentPlayerArea()->getExposedMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($chowPlayerTileCountBefore - 2, $r->getCurrentPlayerArea()->getOnHandTileSortedList()->count());
        $this->assertEquals(0, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());
    }

    function testExposedKong() {
        $r = $this->round;
        // discardPlayer vs chowPlayer
        $discardPlayer = $r->getCurrentPlayer();
        $chowPlayer = $r->getNextPlayer();

        // discard
        $r->getPlayerArea($discardPlayer)->getOnHandTileSortedList()->replaceByIndex(0, \Saki\Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE), $r->getRoundPhase());
        $this->assertEquals(1, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());

        // pong
        $chowPlayerTileCountBefore = $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->count();
        $r->getPlayerArea($chowPlayer)->getOnHandTileSortedList()->replaceByIndex([0,1,2],[Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        $r->exposedKang($r->getNextPlayer());

        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($chowPlayer, $r->getCurrentPlayer());
        $this->assertTrue($r->getCurrentPlayerArea()->getExposedMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($chowPlayerTileCountBefore - 3, $r->getCurrentPlayerArea()->getOnHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayerArea()->hasCandidateTile());
        $this->assertEquals(0, $r->getPlayerArea($discardPlayer)->getDiscardedTileList()->count());
    }
}

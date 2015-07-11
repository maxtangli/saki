<?php

use Saki\Game\PlayerList;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Game\Wall;
use Saki\Meld\Meld;
use Saki\Tile\Tile;

class RoundTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Round
     */
    protected $initialRound;
    /**
     * @var Round
     */
    protected $roundAfterDiscard1m;

    protected function setUp() {
        $playerList = new PlayerList(PlayerList::createPlayers(4, 40000));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];
        $this->initialRound = new Round($wall, $playerList, $dealerPlayer);

        $playerList2 = new PlayerList(PlayerList::createPlayers(4, 40000));
        $wall2 = new Wall(Wall::getStandardTileList());
        $dealerPlayer2 = $playerList2[0];
        $r = new Round($wall2, $playerList2, $dealerPlayer2);
        $discardPlayer = $r->getCurrentPlayer();
        $discardPlayer->getPlayerArea()->getHandTileSortedList()->replaceByIndex(0, Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE), $r->getRoundPhase());
        $this->assertEquals(1, $discardPlayer->getPlayerArea()->getDiscardedTileList()->count());
        $this->roundAfterDiscard1m = $r;
    }

    function testInit() {
        $r = $this->initialRound;

        // phase
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());
        // initial current player
        $this->assertSame($r->getDealerPlayer(), $r->getCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $r->getDealerPlayer()->getPlayerArea()->getHandTileSortedList());
        $this->assertTrue($r->getDealerPlayer()->getPlayerArea()->hasCandidateTile());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $player->getPlayerArea()->getHandTileSortedList();
            $expected = $player == $r->getDealerPlayer() ? 14 : 13;
            $this->assertCount($expected, $onHandTileList, sprintf('%s %s', $player, count($onHandTileList)));
        }
    }

    function testKongBySelf() {
        $r = $this->initialRound;
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0, 1, 2, 3],
            [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count();
        $r->kongBySelf($r->getCurrentPlayer(), Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore - 3, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('(1111m)')));
    }

    function testPlusKongBySelf() {
        $r = $this->initialRound;
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0],
            [Tile::fromString('1m')]);
        $r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count();
        $r->plusKongBySelf($r->getCurrentPlayer(), Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
    }

    function testChowByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextPlayer();
        $actPlayer->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0, 1], [Tile::fromString('2m'), Tile::fromString('3m')]);
        // execute
        $tileCountBefore = $actPlayer->getPlayerArea()->getHandTileSortedList()->count();
        $r->chowByOther($actPlayer, Tile::fromString('2m'), Tile::fromString('3m'));
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getPlayerArea()->getDiscardedTileList()->count());
    }

    function testPongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $actPlayer->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0, 1], [Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getPlayerArea()->getHandTileSortedList()->count();
        $r->pongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getPlayerArea()->getDiscardedTileList()->count());
    }

    function testKongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $actPlayer->getPlayerArea()->getHandTileSortedList()->replaceByIndex([0, 1, 2], [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getPlayerArea()->getHandTileSortedList()->count();
        $r->kongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getPlayerArea()->getDiscardedTileList()->count());
    }

    function testPlusKongByOther() {
        $r = $this->roundAfterDiscard1m;
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getNextNextPlayer();
        $actPlayer->getPlayerArea()->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $actPlayer->getPlayerArea()->getHandTileSortedList()->count();
        $r->plusKongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertFalse($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore + 1, $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getPlayerArea()->getDiscardedTileList()->count());
    }

    function testWinBySelf() {
        // setup
        $r = $this->getWinBySelfRound();
        // execute
        $r->winBySelf($r->getCurrentPlayer());
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::OVER_PHASE), $r->getRoundPhase());
        // score changed
        foreach ($r->getPlayerList() as $player) {
            $scoreDelta = $r->getRoundResult()->getScoreDelta($player);
            $deltaInt = $scoreDelta->getDeltaInt();
            if ($player == $r->getDealerPlayer()) {
                $this->assertGreaterThan(0, $deltaInt);
                $this->assertEquals($scoreDelta->getAfter(), $player->getScore(), $scoreDelta);
            } else {
                $this->assertLessThan(0, $deltaInt);
                $this->assertEquals($scoreDelta->getAfter(), $player->getScore(), $scoreDelta);
            }
        }
    }

    /**
     * @return Round
     */
    protected function getWinBySelfRound() {
        $r = $this->initialRound;
        // setup
        $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->setInnerArray(
            \Saki\Tile\TileList::fromString('123m456m789m123s55s')->toArray()
        );
        $r->getCurrentPlayer()->getPlayerArea()->setCandidateTile(Tile::fromString('1m'));
        return $r;
    }

    function testToNextRound() {
        $r = $this->getWinBySelfRound();
        $dealer = $r->getCurrentPlayer();

        $r->winBySelf($dealer);
        $r->toNextRound();
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());
        $this->assertEquals($dealer, $r->getDealerPlayer());
        // todo initial state
    }

    function testExhaustiveDraw() {
        $r = $this->initialRound;
        for ($phase = $r->getRoundPhase(); $phase != RoundPhase::getOverPhaseInstance(); $phase = $r->getRoundPhase()) {
            if ($phase == RoundPhase::getPrivatePhaseInstance()) {
                $r->discard($r->getCurrentPlayer(), $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()[0]);
            } elseif ($phase == RoundPhase::getPublicPhaseInstance()) {
                $r->passPublicPhase();
            } else {
                throw new \LogicException();
            }
        }

        $this->assertInstanceOf('Saki\Game\RoundResult\ExhaustiveDrawResult', $r->getRoundResult());
    }
}

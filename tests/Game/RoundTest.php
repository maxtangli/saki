<?php

use Saki\Game\MockRound;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\RoundResult\WinRoundResult;
use Saki\Util\MsTimer;

class RoundTest extends PHPUnit_Framework_TestCase {
    function getInitialRound() {
        $r = new Round();
        return $r;
    }
    
    function getRoundAfterDiscard1m() {
        $r = new Round();
        $discardPlayer = $r->getCurrentPlayer();
        $discardPlayer->getTileArea()->getHandTileSortedList()->replaceByIndex(0, Tile::fromString('1m'));
        $r->discard($discardPlayer, Tile::fromString('1m'));
        return $r;
    }
    
    function testInit() {
        $r = $this->getInitialRound();

        // phase
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());
        // initial current player
        $dealerPlayer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        $this->assertSame($dealerPlayer, $r->getCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $dealerPlayer->getTileArea()->getHandTileSortedList());
        $this->assertTrue($r->getRoundData()->getTileAreas()->hasTargetTile()); // $this->assertTrue($dealerPlayer->getTileArea()->hasPrivateTargetTile());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $player->getTileArea()->getHandTileSortedList();
            $expected = $player == $dealerPlayer ? 14 : 13;
            $this->assertCount($expected, $onHandTileList, sprintf('%s %s', $player, count($onHandTileList)));
        }
    }

    function testRoundData() {
        $rd = new \Saki\Game\RoundData();
        $this->assertEquals($rd->getPlayerList()->count(), $rd->getPlayerList()->getLast()->getNo());
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $rd->reset(false);
        }
        $this->assertEquals(4, $rd->getRoundWindData()->getRoundWindTurn());
        $this->assertTrue($rd->getRoundWindData()->isLastOrExtraRound());
        $this->assertFalse($rd->getRoundWindData()->isFinalRound());
    }


    function testGetFinalScoreItems() {
        $r = $this->getInitialRound();
        $scores = [
            31100, 24400, 22300, 22200
        ];
        foreach ($r->getPlayerList() as $k => $player) {
            $player->setScore($scores[$k]);
        }

        $expected = [
            [1, 42],
            [2, 4],
            [3, -18],
            [4, -28],
        ];
        $items = $r->getFinalScoreItems(false);
        foreach ($r->getPlayerList() as $k => $player) {
            $item = $items[$k];
            list($expectedRank, $expectedPoint) = [$expected[$k][0], $expected[$k][1]];
            $this->assertEquals($expectedRank, $item->getRank());
            $this->assertEquals($expectedPoint, $item->getFinalPoint());
        }
    }

    function testKongBySelf() {
        $r = $this->getInitialRound();
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->replaceByIndex([0, 1, 2, 3],
            [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count();
        $r->kongBySelf($r->getCurrentPlayer(), Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore - 3, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('(1111m)')), $r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList());
    }

    function testPlusKongBySelf() {
        $r = $this->getInitialRound();
        // setup
        $actPlayer = $r->getCurrentPlayer();
        $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->replaceByIndex([0],
            [Tile::fromString('1m')]);
        $r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count();
        $r->plusKongBySelf($r->getCurrentPlayer(), Tile::fromString('1m'));
        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
    }

    function testChowByOther() {
        $r = $this->getRoundAfterDiscard1m();
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getRoundData()->getTurnManager()->getOffsetPlayer(1);
        $actPlayer->getTileArea()->getHandTileSortedList()->replaceByIndex([0, 1], [Tile::fromString('2m'), Tile::fromString('3m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandTileSortedList()->count();
        $r->chowByOther($actPlayer, Tile::fromString('2m'), Tile::fromString('3m'));
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedTileList()->count());
    }

    function testPongByOther() {
        $r = $this->getRoundAfterDiscard1m();
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getRoundData()->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getHandTileSortedList()->replaceByIndex([0, 1], [Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandTileSortedList()->count();
        $r->pongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedTileList()->count());
    }

    function testKongByOther() {
        $r = $this->getRoundAfterDiscard1m();
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getRoundData()->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getHandTileSortedList()->replaceByIndex([0, 1, 2], [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandTileSortedList()->count();
        $r->kongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedTileList()->count());
    }

    function testPlusKongByOther() {
        $r = $this->getRoundAfterDiscard1m();
        // setup
        $prePlayer = $r->getCurrentPlayer();
        $actPlayer = $r->getRoundData()->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getDeclaredMeldList()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandTileSortedList()->count();
        $r->plusKongByOther($actPlayer);
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertFalse($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('111m')));
        $this->assertTrue($r->getCurrentPlayer()->getTileArea()->getDeclaredMeldList()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore + 1, $r->getCurrentPlayer()->getTileArea()->getHandTileSortedList()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedTileList()->count());
    }
}

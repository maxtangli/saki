<?php

use Saki\Game\MockRound;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

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
        $this->initialRound = new Round();

        $r = new Round();
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
        $dealerPlayer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        $this->assertSame($dealerPlayer, $r->getCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $dealerPlayer->getPlayerArea()->getHandTileSortedList());
        $this->assertTrue($dealerPlayer->getPlayerArea()->hasPrivateTargetTile());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $player->getPlayerArea()->getHandTileSortedList();
            $expected = $player == $dealerPlayer ? 14 : 13;
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
        $this->assertTrue($r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList()->valueExist(Meld::fromString('(1111m)')), $r->getCurrentPlayer()->getPlayerArea()->getDeclaredMeldList());
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
        $actPlayer = $r->getRoundData()->getPlayerList()->getNextPlayer();
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
        $actPlayer = $r->getPlayerList()->getCurrentOffsetPlayer(2);
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
        $actPlayer = $r->getPlayerList()->getCurrentOffsetPlayer(2);
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
        $actPlayer = $r->getPlayerList()->getCurrentOffsetPlayer(2);
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
        $dealerPlayer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        foreach ($r->getPlayerList() as $player) {
            $scoreDelta = $r->getRoundResult()->getScoreDelta($player);
            $deltaInt = $scoreDelta->getDeltaInt();
            if ($player == $dealerPlayer) {
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
        $r->getCurrentPlayer()->getPlayerArea()->setPrivateTargetTile(Tile::fromString('1m'));
        return $r;
    }

    function testToNextRound() {
        $r = $this->getWinBySelfRound();
        $dealer = $r->getCurrentPlayer();

        $r->winBySelf($dealer);
        $r->toNextRound();
        $this->assertEquals(RoundPhase::getPrivatePhaseInstance(), $r->getRoundPhase());
        $this->assertEquals($dealer, $r->getRoundData()->getPlayerList()->getDealerPlayer());
        // todo test initial state
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

    function testGameOver() {
        $rd = new \Saki\Game\RoundData();
        $rd->reset(false);
        $rd->reset(false);
        $rd->reset(false);
        $r = new Round($rd);
        $this->assertSame($rd, $r->getRoundData());
        $this->assertFalse($r->isGameOver());

        $r->getCurrentPlayer()->getPlayerArea()->getHandTileSortedList()->setInnerArray(
            \Saki\Tile\TileList::fromString('123m456m789m123s55s')->toArray()
        );
        $r->getCurrentPlayer()->getPlayerArea()->setPrivateTargetTile(Tile::fromString('1m'));
        $r->winBySelf($r->getCurrentPlayer());

        $this->assertFalse($r->isGameOver());
        $dealerPlayer = $r->getRoundData()->getPlayerList()->getDealerPlayer();
        $dealerPlayer->setScore('29999');
        $this->assertFalse($r->isGameOver());
        $dealerPlayer->setScore('30000');
        $this->assertTrue($r->isGameOver());
    }

    function testWinRoundResult() {
        // winByOther
        $r = new MockRound();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $r->debugSetHandTileList($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
        $r->winByOther($r->getPlayerList()[1]);
        $this->assertTrue($r->getRoundResult()->getRoundResultType()->isWin());

        // multiWinByOther
        $r = new MockRound();
        $r->debugDiscardByReplace($r->getCurrentPlayer(), Tile::fromString('4s'));
        $r->debugSetHandTileList($r->getPlayerList()[1], TileList::fromString('123m456m789m23s55s'));
        $r->debugSetHandTileList($r->getPlayerList()[2], TileList::fromString('123m456m789m23s55s'));
        $r->multiWinByOther([$r->getPlayerList()[1], $r->getPlayerList()[2]]);
        $this->assertTrue($r->getRoundResult()->getRoundResultType()->isWin());
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

        $cls = get_class(new \Saki\RoundResult\ExhaustiveDrawRoundResult(\Saki\Game\PlayerList::createStandard()->toArray(), [false, false, false, false]));
        $this->assertInstanceOf($cls, $r->getRoundResult());
    }

    function testGetFinalScoreItems() {
        $r = $this->initialRound;
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
}

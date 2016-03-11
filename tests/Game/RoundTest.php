<?php

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\Meld;
use Saki\Tile\Tile;

class RoundTest extends PHPUnit_Framework_TestCase {
    protected function getInitialRound() {
        return new Round();
    }

    protected function getRoundAfterDiscard1m() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');
        return $r;
    }

    function testInit() {
        $r = $this->getInitialRound();

        // phase
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());
        // initial current player
        $dealerPlayer = $r->getPlayerList()->getDealerPlayer();
        $this->assertSame($dealerPlayer, $r->getTurnManager()->getCurrentPlayer());
        // initial candidate tile
        $this->assertCount(14, $dealerPlayer->getTileArea()->getHandReference());
        $this->assertTrue($r->getTileAreas()->hasTargetTile()); // $this->assertTrue($dealerPlayer->getTileArea()->hasPrivateTargetTile());

        // initial on-hand tile count
        foreach ($r->getPlayerList() as $player) {
            $onHandTileList = $player->getTileArea()->getHandReference();
            $expected = $player == $dealerPlayer ? 14 : 13;
            $this->assertCount($expected, $onHandTileList, sprintf('%s %s', $player, count($onHandTileList)));
        }
    }

    function testRoundWindData() {
        $r = $this->getInitialRound();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $r->reset(false);
        }
        $this->assertEquals(4, $r->getRoundWindData()->getRoundWindTurn());
        $this->assertTrue($r->getRoundWindData()->isLastOrExtraRound());
        $this->assertFalse($r->getRoundWindData()->isFinalRound());
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

        // todo should test on GameOver state
        $target = new FinalScoreStrategyTarget($r->getPlayerList());
        $items = $r->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
        foreach ($r->getPlayerList() as $k => $player) {
            $item = $items[$k];
            list($expectedRank, $expectedPoint) = [$expected[$k][0], $expected[$k][1]];
            $this->assertEquals($expectedRank, $item->getRank());
            $this->assertEquals($expectedPoint, $item->getFinalPoint());
        }
    }

    function testConcealedKong() {
        $r = $this->getInitialRound();
        $pro = $r->getProcessor();
        // setup
        $actPlayer = $r->getTurnManager()->getCurrentPlayer();
        $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->replaceByIndex([0, 1, 2, 3],
            [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count();
        $pro->process('concealedKong E 1m');

        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore - 3, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('(1111m)')), $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference());
    }

    function testPlusKong() {
        $r = $this->getInitialRound();
        $pro = $r->getProcessor();
        // setup
        $actPlayer = $r->getTurnManager()->getCurrentPlayer();
        $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->replaceByIndex([0],
            [Tile::fromString('1m')]);
        $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count();
//        $r->plusKongBySelf($r->getTurnManager()->getCurrentPlayer(), Tile::fromString('1m'));
        $pro->process('plusKong E 1m');

        // phase keep
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertEquals($tileCountBefore, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('1111m')));
    }

    function testChow() {
        $r = $this->getRoundAfterDiscard1m();
        $pro = $r->getProcessor();

        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(1);
        $actPlayer->getTileArea()->getHandReference()->replaceByIndex([0, 1], [Tile::fromString('2m'), Tile::fromString('3m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandReference()->count();
//        $r->chow($actPlayer, Tile::fromString('2m'), Tile::fromString('3m'));
        $pro->process('chow S 2m 3m');

        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('123m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedReference()->count());
    }

    function testPong() {
        $r = $this->getRoundAfterDiscard1m();
        $pro = $r->getProcessor();
        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getHandReference()->replaceByIndex([0, 1], [Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandReference()->count();
//        $r->pong($actPlayer);
        $pro->process('pong W');
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedReference()->count());
    }

    function testBigKong() {
        $r = $this->getRoundAfterDiscard1m();
        $pro = $r->getProcessor();
        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getHandReference()->replaceByIndex([0, 1, 2], [Tile::fromString('1m'), Tile::fromString('1m'), Tile::fromString('1m')]);
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandReference()->count();
//        $r->bigKong($actPlayer);
        $pro->process('bigKong W');
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedReference()->count());
    }

    function testSmallKong() {
        $r = $this->getRoundAfterDiscard1m();
        $pro = $r->getProcessor();
        // setup
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(2);
        $actPlayer->getTileArea()->getDeclaredMeldListReference()->push(Meld::fromString('111m'));
        // execute
        $tileCountBefore = $actPlayer->getTileArea()->getHandReference()->count();
//        $r->smallKong($actPlayer);
        $pro->process('smallKong W');
        // phase changed
        $this->assertEquals(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE), $r->getPhaseState()->getRoundPhase());
        $this->assertEquals($actPlayer, $r->getTurnManager()->getCurrentPlayer());
        // tiles moved to created meld
        $this->assertFalse($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('111m')));
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore + 1, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHandReference()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscardedReference()->count());
    }

    function testDebugSkipTo() {
        $r = new Round();
        $playerE = $r->getTurnManager()->getCurrentPlayer();

        // phase not changed
        $targetTile = $r->getTileAreas()->getTargetTile()->getTile()->toNextTile();
        $r->debugSkipTo($playerE, null, null, null);
        $this->assertEquals($playerE, $r->getTurnManager()->getCurrentPlayer());
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());

        // to public phase
        // todo

        // to other player
        // todo
    }
}

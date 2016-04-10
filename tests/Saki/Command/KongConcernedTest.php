<?php

use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Game\GameTurn;
use Saki\Meld\Meld;
use Saki\RoundResult\RoundResultType;

class KongConcernedTest extends PHPUnit_Framework_TestCase {

    function testConcealedKong() {
        $r = new Round();
        $pro = $r->getProcessor();

        // execute
        $area = $r->getTurnManager()->getCurrentPlayer()->getTileArea();
        $currentPlayerBefore = $r->getTurnManager()->getCurrentPlayer();
        $tileCountBefore = $area->getHand()->getPrivate()->count();

        $pro->process('concealedKong E E:s-1111m:1m');

        $currentPlayerAfter = $r->getTurnManager()->getCurrentPlayer();
        $tileCountAfter = $area->getHand()->getPrivate()->count();
        $roundPhaseAfter = $r->getPhaseState()->getRoundPhase();

        // phase keep
        $this->assertEquals(RoundPhase::getPrivateInstance(), $roundPhaseAfter);
        $this->assertEquals($currentPlayerBefore, $currentPlayerAfter);

        // tiles moved to created meld
        $this->assertEquals($tileCountBefore - 3, $tileCountAfter);
        $this->assertTrue($area->getHand()->getDeclare()->valueExist(Meld::fromString('(1111m)')));
    }

    function testPlusKong() {
        $r = new Round();
        $pro = $r->getProcessor();

        // execute
        $pro->process(
            'discard E E:s-1m:1m',
            'mockHand S 11m123456789p13s; pong S'
        );

        $area = $r->getTurnManager()->getCurrentPlayer()->getTileArea();
        $currentPlayerBefore = $r->getTurnManager()->getCurrentPlayer();
        $tileCountBefore = $area->getHand()->getPrivate()->count();

        // robQuad phase
        $pro->process('plusKong S S:s-1m:1m');
        $this->assertEquals(RoundPhase::getPublicInstance(), $r->getPhaseState()->getRoundPhase());
        $this->assertTrue($r->getPhaseState()->isRobQuad());

        // after robQuadPhase
        $pro->process('passAll');
        $currentPlayerAfter = $r->getTurnManager()->getCurrentPlayer();
        $tileCountAfter = $area->getHand()->getPrivate()->count();
        $roundPhaseAfter = $r->getPhaseState()->getRoundPhase();

        // phase keep
        $this->assertEquals(RoundPhase::getPrivateInstance(), $roundPhaseAfter);
        $this->assertEquals($currentPlayerBefore, $currentPlayerAfter);

        // tiles moved to created meld
        $this->assertEquals($tileCountBefore, $tileCountAfter);
        $this->assertTrue($area->getHand()->getDeclare()->valueExist(Meld::fromString('1111m')));
    }

    // todo testPlusKongTargetTile

    function testBigKong() {
        $r = new Round();
        $pro = $r->getProcessor();

        // execute
        $pro->process('discard I I:s-1m:1m');
        $prePlayer = $r->getTurnManager()->getCurrentPlayer();
        $actPlayer = $r->getTurnManager()->getOffsetPlayer(2);
        $area = $actPlayer->getTileArea();
        $tileCountBefore = $area->getHand()->getPublic()->count();

        $pro->process('mockHand W 111m; bigKong W');

        $roundPhaseAfter = $r->getPhaseState()->getRoundPhase();
        $currentPlayerAfter = $r->getTurnManager()->getCurrentPlayer();

        // phase changed
        $this->assertEquals(RoundPhase::getPrivateInstance(), $roundPhaseAfter);
        $this->assertEquals($actPlayer, $currentPlayerAfter);

        // tiles moved to created meld
//        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getDeclaredMeldListReference()->valueExist(Meld::fromString('1111m')));
        $this->assertTrue($r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getDeclare()->valueExist(Meld::fromString('1111m')));
        $this->assertEquals($tileCountBefore - 2, $r->getTurnManager()->getCurrentPlayer()->getTileArea()->getHand()->getPrivate()->count());
        $this->assertEquals(0, $prePlayer->getTileArea()->getDiscard()->count());
    }

    function testNotFourKongDrawBySamePlayer() {
        $r = new Round();
        $r->debugReset(new GameTurn());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s',
            'discard E E:s-1s:1s; passAll'
        );

        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());
    }

    function testFourKongDrawByConcealedKong() {
        $r = new Round();
        $r->debugReset(new GameTurn());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s; passAll',
            'concealedKong N N:s-1111s:1s; discard N N:s-1s:1s; passAll'
        );

        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    function testFourKongDrawByPlusKong() {
        $r = new Round();
        $r->debugReset(new GameTurn());
        $pro = $r->getProcessor();

        $pro->process(
            'discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s; passAll',
            'concealedKong N N:s-1111s:1s; discard N N:s-1m:1m; mockHand E 11m; pong E',
            'plusKong E E:s-1m:1m; passAll; discard E E:s-1m:1m; passAll'
        );

        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }

    function testFourKongDrawByBigKong() {
        $r = new Round();
        $r->debugReset(new GameTurn());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s',
            'mockHand E 111s; bigKong E'
        );
        $this->assertEquals(RoundPhase::getPrivateInstance(), $r->getPhaseState()->getRoundPhase());

        $pro->process('discard E E:s-1s:1s; passAll');
        $this->assertEquals(RoundResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getRoundResult()->getRoundResultType()->getValue());
    }
}
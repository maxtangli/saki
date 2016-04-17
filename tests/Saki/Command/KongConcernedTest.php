<?php

use Saki\Game\Phase;
use Saki\Game\PrevailingStatus;
use Saki\Game\Round;
use Saki\Meld\Meld;
use Saki\Win\Result\ResultType;

class KongConcernedTest extends PHPUnit_Framework_TestCase {
    function testConcealedKong() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('concealedKong E E:s-1111m:1m');
        // todo
    }

    function testPlusKong() {
        // todo detailed test
        $r = new Round();
        $pro = $r->getProcessor();

        // execute
        $pro->process(
            'discard E E:s-1m:1m',
            'mockHand S 11m123456789p13s; pong S'
        );

        $areaBefore = $r->getAreas()->getCurrentArea();
        
        // robQuad phase
        $pro->process('plusKong S S:s-1m:1m');
        $this->assertEquals(Phase::createPublic(), $r->getPhaseState()->getPhase());
        $this->assertTrue($r->getPhaseState()->isRobQuad());

        // after robQuadPhase
        $pro->process('passAll');
        $areaAfter = $r->getAreas()->getCurrentArea();
        $phaseAfter = $r->getPhaseState()->getPhase();

        // phase keep
        $this->assertEquals(Phase::createPrivate(), $phaseAfter);
        $this->assertEquals($areaBefore, $areaAfter);

        // tiles moved to created meld
        $this->assertTrue($areaBefore->getHand()->getDeclare()->valueExist(Meld::fromString('1111m')));
    }

    // todo testPlusKongTargetTile

    function testBigKong() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');
        $pro->process('mockHand W 111m; bigKong W');
        // todo
    }

    function testNotFourKongDrawBySamePlayer() {
        $r = new Round();
        $r->debugInit(PrevailingStatus::createFirst());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s; concealedKong E E:s-1111s:1s',
            'discard E E:s-1s:1s; passAll'
        );

        $this->assertEquals(Phase::createPrivate(), $r->getPhaseState()->getPhase());
    }

    function testFourKongDrawByConcealedKong() {
        $r = new Round();
        $r->debugInit(PrevailingStatus::createFirst());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s; passAll',
            'concealedKong N N:s-1111s:1s; discard N N:s-1s:1s; passAll'
        );

        $this->assertEquals(ResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getResult()->getResultType()->getValue());
    }

    function testFourKongDrawByPlusKong() {
        $r = new Round();
        $r->debugInit(PrevailingStatus::createFirst());
        $pro = $r->getProcessor();

        $pro->process(
            'discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s; passAll',
            'concealedKong N N:s-1111s:1s; discard N N:s-1m:1m; mockHand E 11m; pong E',
            'plusKong E E:s-1m:1m; passAll; discard E E:s-1m:1m; passAll'
        );

        $this->assertEquals(ResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getResult()->getResultType()->getValue());
    }

    function testFourKongDrawByBigKong() {
        $r = new Round();
        $r->debugInit(PrevailingStatus::createFirst());
        $pro = $r->getProcessor();

        $pro->process(
            'concealedKong E E:s-1111s:1s; discard E E:s-1s:1s; passAll',
            'concealedKong S S:s-1111s:1s; discard S S:s-1s:1s; passAll',
            'concealedKong W W:s-1111s:1s; discard W W:s-1s:1s',
            'mockHand E 111s; bigKong E'
        );
        $this->assertEquals(Phase::createPrivate(), $r->getPhaseState()->getPhase());

        $pro->process('discard E E:s-1s:1s; passAll');
        $this->assertEquals(ResultType::FOUR_KONG_DRAW,
            $r->getPhaseState()->getResult()->getResultType()->getValue());
    }
}
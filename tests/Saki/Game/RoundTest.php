<?php

use Saki\FinalPoint\FinalPointStrategyTarget;
use Saki\Game\Phase;
use Saki\Game\Round;

class RoundTest extends SakiTestCase {
    function testNew() {
        $r = new Round();

        // phase
        $this->assertEquals(Phase::createPrivate(), $r->getPhaseState()->getPhase());
        // todo
    }

    function testRoll() {
        // todo
    }

    function testOver() {
        $r = new Round();
        $r->roll(false);
        $r->roll(false);
        $r->roll(false);

        $pro = $r->getProcessor();
        $pro->process('mockHand E 123456789m12355s; tsumo E');
        $this->assertTrue($r->getPhaseState()->isGameOver($r));
        $f = $r->getPhaseState()->getFinalScore($r);
        // todo
    }

    function testChow() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');
        $pro->process('mockHand S 23m; chow S 2m 3m');
        // todo
    }

    function testPung() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('discard I I:s-1m:1m');
        $pro->process('mockHand W 11m123456789p13s; pung W');
        // todo
    }
}

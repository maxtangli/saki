<?php

use Saki\Game\Round;

class SkipCommandTest extends PHPUnit_Framework_TestCase {
    function testSkip() {
        $r = new Round();
        $pro = $r->getProcessor();

        $this->assertEquals('E', $r->getTurnManager()->getCurrentTurn()->getSeatWind());
        $pro->process('skip 1');
        $this->assertEquals('S', $r->getTurnManager()->getCurrentTurn()->getSeatWind());
        $this->assertTrue($r->getPhaseState()->getPhase()->isPrivate());

        $pro->process('skip 2');
        $this->assertEquals('N', $r->getTurnManager()->getCurrentTurn()->getSeatWind());
        $this->assertTrue($r->getPhaseState()->getPhase()->isPrivate());
    }
}
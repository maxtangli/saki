<?php

use Saki\Game\Round;

class SkipCommandTest extends PHPUnit_Framework_TestCase {
    function testSkip() {
        $r = new Round();
        $pro = $r->getProcessor();

        $this->assertEquals('E', $r->getAreas()->getCurrentTurn()->getSeatWind());
        $pro->process('skip 1');
        $this->assertEquals('S', $r->getAreas()->getCurrentTurn()->getSeatWind());
        $this->assertTrue($r->getPhaseState()->getPhase()->isPrivate());

        $pro->process('skip 2');
        $this->assertEquals('N', $r->getAreas()->getCurrentTurn()->getSeatWind());
        $this->assertTrue($r->getPhaseState()->getPhase()->isPrivate());
    }
}
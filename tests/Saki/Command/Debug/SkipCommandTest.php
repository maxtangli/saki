<?php

use Saki\Game\Round;

class SkipCommandTest extends SakiTestCase {
    function testSkip() {
        $r = new Round();

        $this->assertEquals('E', $r->getAreas()->getTurn()->getSeatWind());
        $r->process('skip 1');
        $this->assertEquals('S', $r->getAreas()->getTurn()->getSeatWind());
        $this->assertTrue($r->getAreas()->getPhaseState()->getPhase()->isPrivate());

        $r->process('skip 2');
        $this->assertEquals('N', $r->getAreas()->getTurn()->getSeatWind());
        $this->assertTrue($r->getAreas()->getPhaseState()->getPhase()->isPrivate());
    }
}
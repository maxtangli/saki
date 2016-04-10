<?php

use Saki\Game\Round;

class SkipCommandTest extends PHPUnit_Framework_TestCase {
    function testSkip() {
        $r = new Round();
        $pro = $r->getProcessor();

        $this->assertEquals('E', $r->getTurnManager()->getCurrentPlayerWind());
        $pro->process('skip 1');
        $this->assertEquals('S', $r->getTurnManager()->getCurrentPlayerWind());
        $this->assertTrue($r->getPhaseState()->getRoundPhase()->isPrivate());

        $pro->process('skip 2');
        $this->assertEquals('N', $r->getTurnManager()->getCurrentPlayerWind());
        $this->assertTrue($r->getPhaseState()->getRoundPhase()->isPrivate());
    }
}
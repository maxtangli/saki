<?php

use Saki\Game\Round;

class PrevailingCurrentTest extends PHPUnit_Framework_TestCase {
    function testPrevailingCurrent() {
        $r = new Round();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $r->roll(false);
        }

        $current = $r->getPrevailingCurrent();
        $this->assertEquals(4, $current->getStatus()->getPrevailingWindTurn());
        $this->assertFalse($r->getPrevailingCurrent()->isSuddenDeathLast());
        // todo detailed test
    }
}

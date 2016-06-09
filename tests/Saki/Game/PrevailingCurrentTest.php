<?php

use Saki\Game\Round;

class PrevailingCurrentTest extends SakiTestCase {
    function testPrevailingCurrent() {
        $r = new Round();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $r->roll(false);
        }

        $current = $r->getAreas()->getPrevailingCurrent();
        $this->assertEquals(4, $current->getStatus()->getPrevailingWindTurn());
        $this->assertFalse($r->getAreas()->getPrevailingCurrent()->isSuddenDeathLast());
        // todo detailed test
    }
}

<?php

use Saki\Game\Round;

class PrevailingCurrentTest extends \SakiTestCase {
    function testPrevailingCurrent() {
        $round = $this->getInitRound();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $round->roll(false);
        }

        $current = $round->getPrevailingCurrent();
        $this->assertEquals(4, $current->getStatus()->getPrevailingWindTurn());
        $this->assertFalse($round->getPrevailingCurrent()->isSuddenDeathLast());
        // todo detailed test
    }
}

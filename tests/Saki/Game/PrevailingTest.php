<?php

class PrevailingTest extends \SakiTestCase {
    function testPrevailing() {
        $round = $this->getInitRound();
        for ($nTodo = 3; $nTodo > 0; --$nTodo) {
            $round->roll(false);
        }

        $current = $round->getPrevailing();
        $this->assertEquals(4, $current->getStatus()->getPrevailingWindTurn());
        $this->assertFalse($round->getPrevailing()->isSuddenDeathLast());
        
        $this->assertEquals('E,4th,0 continue', $current->__toString());
        // todo detailed test
    }
}

<?php

use Saki\Game\PlayerList;

class PlayerListTest extends PHPUnit_Framework_TestCase {
    function testInitialState() {
        $m = new PlayerList(4, 10000);
        list($p1, $p2, $p3, $p4) = $m->toArray();
        $this->assertEquals($p1, $m->getCurrentPlayer());
        $this->assertEquals($p2, $m->getNextPlayer());
        $this->assertEquals($p3, $m->getOffsetPlayer(2));
    }

    /**
     * @depends testInitialState
     */
    function testToNext() {
        $m = new PlayerList(4, 10000);
        list($p1, $p2, $p3, $p4) = $m->toArray();
        $m->toPlayer($p3);
        $expectedPlayer = [$p3, $p4, $p1, $p2];
        for ($i = 0; $i < count($expectedPlayer); ++$i) {
            $this->assertSame($expectedPlayer[$i], $m->getCurrentPlayer(), sprintf('[%s] vs [%s]', $expectedPlayer[$i], $m->getCurrentPlayer()));
            $this->assertSame(1, $m->getCurrentPlayer()->getTurn());
            $m->toNextPlayer();
        }
    }
}

<?php

use Saki\Game\PlayerList;

class PlayerListTest extends PHPUnit_Framework_TestCase {
    function testInitialState() {
        $players = PlayerList::createPlayers(3, 10000);
        $m = new PlayerList($players);
        list($p1, $p2, $p3) = $m->toArray();
        $this->assertEquals($p1, $m->getCurrentPlayer());
        $this->assertEquals($p2, $m->getNextPlayer());
        $this->assertEquals($p3, $m->getNextNextPlayer());
    }

    /**
     * @depends testInitialState
     */
    function testToNext() {
        $players = PlayerList::createPlayers(3, 10000);
        $m = new PlayerList($players);
        list($p1, $p2, $p3) = $m->toArray();
        $m->toPlayer($p3);
        $expectedPlayer = [$p3, $p1, $p2];
        for ($i = 0; $i < count($players); ++$i) {
            $this->assertSame($expectedPlayer[$i], $m->getCurrentPlayer(), sprintf('[%s] vs [%s]', $expectedPlayer[$i], $m->getCurrentPlayer()));
            $this->assertSame(1, $m->getCurrentPlayer()->getTurn());
            $m->toNextPlayer();
        }
    }
}

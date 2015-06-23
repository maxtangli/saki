<?php


class TurnManagerTest extends PHPUnit_Framework_TestCase {
    function testInitialState() {
        $players = ['saki', 'nodoka', 'koromo'];
        $m = new \Saki\Game\TurnManager($players, 'saki', 0);
        $this->assertEquals($players, $m->getPlayers());
        $this->assertEquals(3, $m->getPlayerCount());
        $this->assertEquals('saki', $m->getCurrentPlayer());
        $this->assertEquals('nodoka', $m->getNextPlayer());
        $this->assertEquals(0, $m->getCurrentPlayerTurn());
        $this->assertEquals(0, $m->getTotalTurn());
    }

    /**
     * @depends testInitialState
     */
    function testToNext() {
        $players = ['saki', 'nodoka', 'koromo'];
        $m = new \Saki\Game\TurnManager($players, 'saki', 0);
        $m->toPlayer('koromo');
        $expectedPlayer = ['koromo','saki', 'nodoka'];
        for ($i = 0; $i < count($players); ++$i) {
            $this->assertSame($expectedPlayer[$i], $m->getCurrentPlayer());
            $this->assertSame(1, $m->getCurrentPlayerTurn());
            $this->assertSame($i+1, $m->getTotalTurn());
            $m->toNextPlayer();
        }
    }
}

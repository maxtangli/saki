<?php


class TurnManagerTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $players = ['saki', 'nodoka', 'koromo'];
        $currentPlayer = 'koromo';
        // new
        $m = new \Saki\Game\TurnManager($players, $currentPlayer);
        $this->assertSame($currentPlayer, $m->getCurrentPlayer());

        // toNextPlayer
        $m = new \Saki\Game\TurnManager($players, $currentPlayer, 0);
        $expectedPlayer = ['koromo', 'saki', 'nodoka'];
        for ($i = 0; $i < count($players); ++$i) {
            $this->assertSame($expectedPlayer[$i], $m->getCurrentPlayer());
            $this->assertSame($i, $m->getCurrentTurn());
            $m->toNextPlayer();
        }

        // toPlayer
        $m = new \Saki\Game\TurnManager($players, $currentPlayer, 1);
        $m->toPlayer('saki');
        $this->assertSame('saki', $m->getCurrentPlayer());
        $this->assertSame(2, $m->getCurrentTurn());
    }
}

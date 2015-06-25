<?php

class CommandFactoryTest extends PHPUnit_Framework_TestCase {
    function testCreate() {
        $game = new \Saki\Game\Game(4,10000);
        $f = new \Saki\Command\CommandFactory($game);

        $r= $game->getCurrentRound();
        $p = $r->getCurrentPlayer();
        $t = $r->getPlayerArea($p)->getHandTileSortedList()[0];

        $s = implode(' ', ['discard', $p, $t]);
        $command = $f->createCommand($s);
        $this->assertInstanceOf('\Saki\Command\DiscardCommand', $command);
        $this->assertEquals($t, $command->getTile());

        //$r->acceptCommand($command);
    }
}

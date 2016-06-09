<?php

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class DiscardCommandTest extends SakiTestCase {
    function testAll() {
        $r = new Round();

        $r->process('mockHand E 123456789m12344s');

        $context = new CommandContext($r);
        $invalidCommand = new DiscardCommand($context, SeatWind::fromString('E'), Tile::fromString('9p'));
        $this->assertFalse($invalidCommand->executable());

        $validCommand = new DiscardCommand($context, SeatWind::fromString('E'), Tile::fromString('4s'));
        $this->assertTrue($validCommand->executable());

        $validCommand->execute();
        $this->assertEquals(Phase::createPublic(), $r->getAreas()->getPhaseState()->getPhase());
    }
}
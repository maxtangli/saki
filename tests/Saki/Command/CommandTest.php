<?php

use Saki\Command\Debug\MockHandCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\TsumoCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Game\Phase;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class CommandTest extends \SakiTestCase {
    function testIsDebug() {
        $this->assertFalse(DiscardCommand::isDebug());
        $this->assertTrue(MockHandCommand::isDebug());
    }

    function testIsRon() {
        $this->assertFalse(TsumoCommand::isRon());
        $this->assertTrue(RonCommand::isRon());
    }

    function testDiscard() {
        $round = $this->getInitRound();

        $round->process('mockHand E 123456789m12344s');

        $invalidCommand = new DiscardCommand($round, [SeatWind::fromString('E'), Tile::fromString('9p')]);
        $this->assertFalse($invalidCommand->executable());

        $validCommand = new DiscardCommand($round, [SeatWind::fromString('E'), Tile::fromString('4s')]);
        $this->assertTrue($validCommand->executable());

        $validCommand->execute();
        $this->assertEquals(Phase::createPublic(), $round->getPhaseState()->getPhase());
    }

    function testSkip() {
        $round = $this->getInitRound();

        $this->assertEquals('E', $round->getTurn()->getSeatWind());
        $round->process('skip 1');
        $this->assertEquals('S', $round->getTurn()->getSeatWind());
        $this->assertTrue($round->getPhaseState()->getPhase()->isPrivate());

        $round->process('skip 2');
        $this->assertEquals('N', $round->getTurn()->getSeatWind());
        $this->assertTrue($round->getPhaseState()->getPhase()->isPrivate());
    }
}
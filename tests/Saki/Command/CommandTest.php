<?php

use Saki\Command\Debug\MockHandCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PublicCommandDecider;
use Saki\Game\Phase;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;

class CommandTest extends \SakiTestCase {
    function testIsDebug() {
        $this->assertFalse(DiscardCommand::isDebug());
        $this->assertTrue(MockHandCommand::isDebug());
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

    function testDecider() {
        $round = $this->getInitRound();
        $round->process('mockHand E 1m; discard E 1m');

        $parser = $round->getProcessor()->getParser();
        $chow = $parser->parseLine('chow S 23m');
        $pung = $parser->parseLine('pung W 11m');
    }
}
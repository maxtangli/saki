<?php

use Saki\Command\Debug\MockHandCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\BufferCommandDecider;
use Saki\Game\Phase;
use Saki\Game\SeatWind;
use Saki\Game\Tile\Tile;

class CommandTest extends \SakiTestCase {
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
        $chowS = $parser->parseLine('chow S 23m');
        $pungW = $parser->parseLine('pung W 11m');
        $passS = $parser->parseLine('pass S');
        $passW = $parser->parseLine('pass W');
        $passN = $parser->parseLine('pass N');
        $passAll = $parser->parseLine('passAll');

        // test passAll
        $decider = new BufferCommandDecider($round->getRule()->getPlayerType(), $round->getProcessor()->getParser());
        $decider->submit($passS);
        $decider->submit($passW);
        $decider->submit($passN);
        $this->assertEquals($passAll, $decider->getDecided());

        // test replace
        $decider->clear();
        $decider->submit($passN);
        $decider->submit($chowS);
        $decider->submit($pungW);
        $this->assertFalse($decider->allowSubmit($chowS));
        $decider->submit($passS);
        $decider->submit($passN);
        $this->assertEquals($pungW, $decider->getDecided());
    }
}
<?php

use Saki\Command\DebugCommand\MockHandCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\BufferedCommandDecider;
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

        $this->assertEquals('E', $round->getTurnHolder()->getTurn()->getSeatWind());
        $round->process('skip 1');
        $this->assertEquals('S', $round->getTurnHolder()->getTurn()->getSeatWind());
        $this->assertTrue($round->getPhaseState()->getPhase()->isPrivate());

        $round->process('skip 2');
        $this->assertEquals('N', $round->getTurnHolder()->getTurn()->getSeatWind());
        $this->assertTrue($round->getPhaseState()->getPhase()->isPrivate());
    }

    function testDecider() {
        $round = $this->getInitRound();
        $round->getDebugConfig()->enableDecider(false);
        $round->process('mockHand E 1m; discard E 1m');

        // test passAll
        $round->process('pass S; pass W; pass N');
        $this->assertPrivate('S');

        // test replace
        $round->process(
            'skipTo E true; mockHand E 1m; discard E 1m; mockHand S 23m; mockHand W 11m',
            'pass N; chow S 23m; pung W 11m'
        );
        $this->assertPrivate('W');

        // test not executable by priority
        $round->process(
            'skipTo E true; mockHand E 1m; discard E 1m; mockHand S 23m; mockHand W 11m',
            'pass N; pung W 11m'
        );
        $this->assertNotExecutable('chow S 23m');
    }
}
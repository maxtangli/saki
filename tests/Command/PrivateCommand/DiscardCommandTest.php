<?php

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Tile\TileList;

class DiscardCommandTest extends PHPUnit_Framework_TestCase {
    function testAll() {
        $r = new Round();
        $r->getTileAreas()->debugReplaceHand($r->getTurnManager()->getCurrentPlayer(), TileList::fromString('123456789m12344s'));

        $context = new CommandContext($r);
        $invalidCommand = DiscardCommand::fromString($context, 'discard E 9p');
        $this->assertFalse($invalidCommand->executable());

        $validCommand = DiscardCommand::fromString($context, 'discard E 4s');
        $this->assertTrue($validCommand->executable());

        $validCommand->execute();
        $this->assertEquals(RoundPhase::getPublicInstance(), $r->getPhaseState()->getRoundPhase());
    }
}
<?php

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Round;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class TileParamDeclarationTest extends PHPUnit_Framework_TestCase {
    function testAll() {
        $r = new Round();
        $context = new CommandContext($r);

        $r->getTileAreas()->debugReplaceHand($r->getTurnManager()->getCurrentPlayer(), TileList::fromString('123456789s1122m'));
        DiscardCommand::fromString($context, 'discard E E:s-E:E')->execute();
        $this->assertEquals($r->getTileAreas()->getOpenHistory()->getAll()[0], Tile::fromString('E'));

        // other tests ignored
    }
}
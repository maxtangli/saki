<?php

use Saki\Command\CommandContext;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Round;
use Saki\Game\RoundData;
use Saki\Game\RoundDebugResetData;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class TileParamDeclarationTest extends PHPUnit_Framework_TestCase {
    function testAll() {
        $r = new Round();
        $rd = $r->getRoundData();
        $context = new CommandContext($rd);

        $rd->getTileAreas()->debugReplaceHand($r->getCurrentPlayer(), TileList::fromString('123456789s1122m'));
        DiscardCommand::fromString($context, 'discard E E:s-E:E')->execute();
        $this->assertEquals($rd->getTileAreas()->getDiscardHistory()->getAllDiscardTileList()[0], Tile::fromString('E'));

        // other tests ignored
    }
}
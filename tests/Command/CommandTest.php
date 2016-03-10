<?php

namespace tests\Command;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Game\Round;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

class HelloCommand extends Command {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile) {
        parent::__construct($context, [$playerSelfWind, $tile]);
    }

    /**
     * @return Tile
     */
    function getPlayerSelfWind() {
        return $this->playerSelfWind;
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->tile;
    }

    function executable() {
        return true;
    }

    function executeImpl() {
        return 'hello';
    }
}

class CommandTest extends \PHPUnit_Framework_TestCase {
    function testParse() {
        $context = new CommandContext(new RoundData());
        $parser = new CommandParser($context, [HelloCommand::class]);

        // parseLine
        $line = 'hello E 1p';
        $obj = HelloCommand::fromString($context, $line);
        $this->assertEquals($obj->__toString(), $line);

        $obj2 = $parser->parseLine($line);
        $this->assertEquals($obj2->__toString(), $line);

        // parseScript
        $objects = $parser->parseScript($line);
        $this->assertCount(1, $objects);
        $this->assertEquals($objects[0]->__toString(), $line);

        $script = 'hello E 1p; hello E 1p';
        $objects = $parser->parseScript($script);
        $this->assertCount(2, $objects);
        $this->assertEquals($objects[0]->__toString(), $line);
        $this->assertEquals($objects[1]->__toString(), $line);
    }

    function testDiscardCommand() {
        $r = new Round();
        $r->getRoundData()->getTileAreas()->debugReplaceHand($r->getCurrentPlayer(), TileList::fromString('123456789m12344s'));

        $context = new CommandContext($r->getRoundData());
        $invalidCommand = DiscardCommand::fromString($context, 'discard E 9p');
        $this->assertFalse($invalidCommand->executable());

        $validCommand = DiscardCommand::fromString($context, 'discard E 4s');
        $this->assertTrue($validCommand->executable());

        $validCommand->execute();
        $this->assertEquals(RoundPhase::getPublicInstance(), $r->getRoundPhase());
    }
}
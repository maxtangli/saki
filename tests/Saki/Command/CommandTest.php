<?php

namespace tests\Command;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandSet;
use Saki\Command\Debug\MockHandCommand;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\TsumoCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class HelloCommand extends Command {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $actor, Tile $tile) {
        parent::__construct($context, [$actor, $tile]);
    }

    /**
     * @return Tile
     */
    function getPlayerSeatWind() {
        return $this->playerSeatWind;
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->tile;
    }

    protected function executableImpl(CommandContext $context) {
        return true;
    }

    protected function executeImpl(CommandContext $context) {
        return 'hello';
    }
}

class CommandTest extends \PHPUnit_Framework_TestCase {
    function testParse() {
        $context = new CommandContext(new Round());
        $set = new CommandSet([HelloCommand::class]);
        $parser = new CommandParser($context, $set);

        // parseLine
        $line = 'hello E 1p';
        $obj = new HelloCommand($context, SeatWind::fromString('E'), Tile::fromString('1p'));
        $this->assertEquals($obj->__toString(), $line);

        $obj2 = $parser->parseLine($line);
        $this->assertEquals($obj2->__toString(), $line);
    }

    function testIsDebug() {
        $this->assertFalse(DiscardCommand::isDebug());
        $this->assertTrue(MockHandCommand::isDebug());
    }

    function testIsRon() {
        $this->assertFalse(TsumoCommand::isRon());
        $this->assertTrue(RonCommand::isRon());
    }
}
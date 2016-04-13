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
use Saki\Command\PrivateCommand\WinBySelfCommand;
use Saki\Command\PublicCommand\WinByOtherCommand;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Tile\Tile;

class HelloCommand extends Command {
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, SeatWind $playerSeatWind, Tile $tile) {
        parent::__construct($context, [$playerSeatWind, $tile]);
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
        $obj = HelloCommand::fromString($context, $line);
        $this->assertEquals($obj->__toString(), $line);

        $obj2 = $parser->parseLine($line);
        $this->assertEquals($obj2->__toString(), $line);

        // scriptToLine

        $script = 'hello E 1p; hello E 1p';
        $lines = $parser->scriptToLines($script);
        $this->assertCount(2, $lines);
        $this->assertEquals($lines[0], 'hello E 1p');
        $this->assertEquals($lines[1], 'hello E 1p');
    }

    function testIsDebug() {
        $this->assertFalse(DiscardCommand::isDebug());
        $this->assertTrue(MockHandCommand::isDebug());
    }

    function testIsWinByOther() {
        $this->assertFalse(WinBySelfCommand::isWinByOther());
        $this->assertTrue(WinByOtherCommand::isWinByOther());
    }
}
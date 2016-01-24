<?php

namespace tests\Command;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;
use Saki\Command\ParamDeclaration\TileParamDeclaration;
use Saki\Game\Round;
use Saki\Tile\Tile;
use Saki\Util\MsTimer;

class HelloCommand extends Command {
    static function getParamDeclarations() {
        return [SelfWindParamDeclaration::class, TileParamDeclaration::class];
    }

    function __construct(CommandContext $context, Tile $playerSelfWind, Tile $tile) {
        parent::__construct($context, [$playerSelfWind, $tile]);
        // todo validate
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

    function execute() {
        return 'hello';
    }
}

class CommandTest extends \PHPUnit_Framework_TestCase {
    function testParse() {
        $context = new CommandContext(new Round());

        MsTimer::getInstance()->restart();
        $line = 'hello E 1p';
        $obj = HelloCommand::fromString($context, $line);
        MsTimer::getInstance()->restartWithDump();

        $this->assertEquals($obj->__toString(), $line);
    }
}
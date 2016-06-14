<?php

namespace tests\Command;

use Saki\Command\Command;
use Saki\Command\CommandProvider;
use Saki\Command\CommandSet;
use Saki\Game\SeatWind;

class CommandProviderTest extends \SakiTestCase {
    /**
     * @param string[] $expected
     * @param Command[] $commands
     */
    protected function assertCommands(array $expected, array $commands) {
        $commandStrings = array_map(function (Command $command) {
            return $command->__toString();
        }, $commands);
        $this->assertEquals($expected, $commandStrings);
    }

    /**
     * @param string[] $expected
     * @param string $actor
     */
    protected function assertExecutables(array $expected, string $actor) {
        $provider = new CommandProvider($this->getCurrentRound(), CommandSet::createStandard());
        $executables = $provider->getExecutables(SeatWind::fromString($actor));
        $this->assertCommands($expected, $executables);
    }

    function testDiscard() {
        $round = $this->getInitRound();
        $round->process('mockHand E 33334444555066m');
        $this->assertExecutables(['discard E 3m', 'discard E 4m', 'discard E 5m', 'discard E 0m', 'discard E 6m'], 'E');
        $this->assertExecutables([], 'S');
    }
}

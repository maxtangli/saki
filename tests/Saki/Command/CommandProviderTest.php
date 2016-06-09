<?php

namespace tests\Command;

use Saki\Command\CommandContext;
use Saki\Command\CommandProvider;
use Saki\Command\CommandSet;

class CommandProviderTest extends \SakiTestCase {
    function testAll() {
        $r = $this->getInitRound();
        $p = new CommandProvider(new CommandContext($r), CommandSet::createStandard());
//        $this->assertNotEmpty($p->getExecutables(SeatWind::createEast()));
//        $this->assertEmpty($p->getExecutables(SeatWind::createSouth()));
    }
}

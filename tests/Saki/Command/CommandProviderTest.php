<?php

namespace tests\Command;

use Saki\Command\CommandProvider;
use Saki\Command\CommandSet;

class CommandProviderTest extends \SakiTestCase {
    function testAll() {
        $round = $this->getInitRound();
        $p = new CommandProvider($round, CommandSet::createStandard());
//        $this->assertNotEmpty($p->getExecutables(SeatWind::createEast()));
//        $this->assertEmpty($p->getExecutables(SeatWind::createSouth()));
    }
}

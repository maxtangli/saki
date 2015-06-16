<?php

use Saki\Game\PublicPhaseCommandPoller;

class PublicPhaseCommandPollerTest extends PHPUnit_Framework_TestCase {
    function testOverall() {
        $p = new PublicPhaseCommandPoller([]);
        $this->assertTrue($p->isDecided());
    }
}
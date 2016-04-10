<?php

use Saki\Game\DeclareHistory;
use Saki\Game\SeatWind;
use Saki\Game\Turn;

class DeclareHistoryTest extends PHPUnit_Framework_TestCase {
    function testAll() {
        $h = new DeclareHistory();
        $h->recordDeclare(new Turn(2, SeatWind::createSouth()));
        $h->recordDeclare(new Turn(4, SeatWind::createWest()));
        $this->assertTrue($h->hasDeclare(new Turn(1, SeatWind::createNorth())));
        $this->assertTrue($h->hasDeclare(new Turn(2, SeatWind::createSouth())));
        $this->assertTrue($h->hasDeclare(new Turn(2, SeatWind::createNorth())));
        $this->assertTrue($h->hasDeclare(new Turn(3, SeatWind::createEast())));
        $this->assertTrue($h->hasDeclare(new Turn(4, SeatWind::createWest())));
        $this->assertFalse($h->hasDeclare(new Turn(4, SeatWind::createNorth())));
        $this->assertFalse($h->hasDeclare(new Turn(5, SeatWind::createEast())));
    }
}

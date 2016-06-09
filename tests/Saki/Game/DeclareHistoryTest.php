<?php

use Saki\Game\ClaimHistory;
use Saki\Game\SeatWind;
use Saki\Game\Turn;

class ClaimHistoryTest extends \SakiTestCase {
    function testAll() {
        $h = new ClaimHistory();
        $h->recordClaim(new Turn(2, SeatWind::createSouth()));
        $h->recordClaim(new Turn(4, SeatWind::createWest()));
        $this->assertTrue($h->hasClaim(new Turn(1, SeatWind::createNorth())));
        $this->assertTrue($h->hasClaim(new Turn(2, SeatWind::createSouth())));
        $this->assertTrue($h->hasClaim(new Turn(2, SeatWind::createNorth())));
        $this->assertTrue($h->hasClaim(new Turn(3, SeatWind::createEast())));
        $this->assertTrue($h->hasClaim(new Turn(4, SeatWind::createWest())));
        $this->assertFalse($h->hasClaim(new Turn(4, SeatWind::createNorth())));
        $this->assertFalse($h->hasClaim(new Turn(5, SeatWind::createEast())));
    }
}

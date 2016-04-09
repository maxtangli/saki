<?php

use Saki\Game\DeclareHistory;
use Saki\Game\PlayerWind;
use Saki\Game\Round;
use Saki\Game\RoundTurn;

class DeclareHistoryTest extends PHPUnit_Framework_TestCase {

    function testAll() {
        $h = new DeclareHistory();
        $h->recordDeclare(new RoundTurn(2, PlayerWind::createSouth()));
        $h->recordDeclare(new RoundTurn(4, PlayerWind::createWest()));
        $this->assertTrue($h->hasDeclare(new RoundTurn(1, PlayerWind::createNorth())));
        $this->assertTrue($h->hasDeclare(new RoundTurn(2, PlayerWind::createSouth())));
        $this->assertTrue($h->hasDeclare(new RoundTurn(2, PlayerWind::createNorth())));
        $this->assertTrue($h->hasDeclare(new RoundTurn(3, PlayerWind::createEast())));
        $this->assertTrue($h->hasDeclare(new RoundTurn(4, PlayerWind::createWest())));
        $this->assertFalse($h->hasDeclare(new RoundTurn(4, PlayerWind::createNorth())));
        $this->assertFalse($h->hasDeclare(new RoundTurn(5, PlayerWind::createEast())));
    }
}

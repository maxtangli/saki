<?php

use Saki\Game\RoundWindTurnData;
use Saki\Tile\Tile;

class RoundWindTurnDataTest extends PHPUnit_Framework_TestCase {
    function testGetDealerWind() {
        $a = new RoundWindTurnData(1);
        $a->setDealerWind(Tile::fromString('W'));
        $this->assertEquals(2, $a->getTurn());
    }
}
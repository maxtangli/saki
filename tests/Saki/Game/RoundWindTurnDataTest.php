<?php

use Saki\Game\PrevailingWindTurnData;
use Saki\Tile\Tile;

class PrevailingWindTurnDataTest extends PHPUnit_Framework_TestCase {
    function testGetDealerWind() {
        $a = new PrevailingWindTurnData(1);
        $a->setDealerWind(Tile::fromString('W'));
        $this->assertEquals(2, $a->getTurn());
    }
}
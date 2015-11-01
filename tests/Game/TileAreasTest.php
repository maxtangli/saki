<?php

use Saki\Game\MockRound;

class TileAreasTest extends PHPUnit_Framework_TestCase {
    function testFirstTurnWin() {
        $r = new MockRound();
        $t = $r->getRoundData()->getTileAreas();
    }
}
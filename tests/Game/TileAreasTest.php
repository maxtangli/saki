<?php

use Saki\Game\Round;

class TileAreasTest extends PHPUnit_Framework_TestCase {
    function testFirstTurnWin() {
        $r = new Round();
        $t = $r->getTileAreas();
    }
}
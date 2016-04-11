<?php

use Saki\Game\Round;

class PlayerListTest extends PHPUnit_Framework_TestCase {
    function testTiledForTop() {
        $l = (new Round())->getPlayerList();
        $this->assertTrue($l->areTiledForTop());

        $area = $l->getEastPlayer()->getArea();
        $area->setPoint($area->getPoint() + 1000);
        $this->assertFalse($l->areTiledForTop());
    }
}

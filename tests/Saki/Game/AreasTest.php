<?php

use Saki\Game\Round;
use Saki\Game\SeatWind;

class AreasTest extends PHPUnit_Framework_TestCase {
    function testGetHand() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('mockHand E 123456789m12344p');
        $areaE = $r->getAreas()->getArea(SeatWind::createEast());
        $areaS = $r->getAreas()->getArea(SeatWind::createSouth());

        // E private phase, hand E
        $handE = $areaE->getHand();
        $this->assertEquals('123456789m12344p', $handE->getPrivate()->toFormatString(true));
        $this->assertEquals('4p', $handE->getTarget()->getTile()->__toString());
        $this->assertEquals('123456789m1234p', $handE->getPublic()->toFormatString(true));

        // E private phase, hand S
        $pro->process('mockHand S 123456789p1234s');
        $handS = $areaS->getHand();
        // no private
        $this->assertFalse($handS->getTarget()->exist());
        $this->assertEquals('123456789p1234s', $handS->getPublic()->toFormatString(true));

        // E public phase, hand E
        $pro->process('discard E 2m');
        $handE = $areaE->getHand();
        // no private
        $this->assertFalse($handE->getTarget()->exist());
        $this->assertEquals('13456789m12344p', $handE->getPublic()->toFormatString(true));

        // E public phase, hand S
        $handS = $areaS->getHand();
        $this->assertEquals('2m123456789p1234s', $handS->getPrivate()->toFormatString(true));
        $this->assertEquals('2m', $handS->getTarget()->getTile()->toFormatString(true));
        $this->assertEquals('123456789p1234s', $handS->getPublic()->toFormatString(true));
    }

    function testPointList() {
        $r = new Round();
        $areas = $r->getAreas();
        $facade = $areas->getPointList();

        $this->assertFalse($facade->hasMinus());
        $this->assertTrue($facade->hasTiledTop());
        $this->assertEquals(25000, $facade->getFirst()->getPoint());
    }
}
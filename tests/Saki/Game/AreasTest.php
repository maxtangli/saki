<?php

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Tile\Tile;

class AreasTest extends PHPUnit_Framework_TestCase {
    function testGetHand() {
        $r = new Round();
        $pro = $r->getProcessor();
        $pro->process('mockHand E 123456789m12344p');
        $areaE = $r->getPlayerList()->getDealerPlayer()->getArea();
        $areaS = $r->getPlayerList()->getSouthPlayer()->getArea();

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

    function testSeatWindAssign() {
        $r = new Round();
        $initialWindTileList = $r->getPlayerList()->toArrayList(function (Player $player) {
            return $player->getArea()->getSeatWind()->getWindTile();
        });
        $this->assertEquals(Tile::getWindList()->toArray(), $initialWindTileList->toArray());
    }
}